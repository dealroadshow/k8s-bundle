<?php

namespace Dealroadshow\Bundle\K8SBundle\Middleware;

use Dealroadshow\Bundle\K8SBundle\EnvManagement\Attribute\AfterMethod;
use Dealroadshow\Bundle\K8SBundle\EnvManagement\Attribute\BeforeMethod;
use Dealroadshow\Bundle\K8SBundle\Util\AttributesUtil;
use Dealroadshow\K8S\Framework\Core\ManifestInterface;
use Dealroadshow\K8S\Framework\Middleware\AbstractManifestMiddleware;
use Dealroadshow\K8S\Framework\Util\ReflectionUtil;
use LogicException;
use ReflectionException;
use ReflectionObject;

class ManifestMethodWrappersMiddleware extends AbstractManifestMiddleware
{
    public function __construct(private string $env)
    {
    }

    /**
     * @param ManifestInterface $manifest
     * @param string            $methodName
     * @param array             $params
     * @param mixed             $returnValue
     *
     * @throws ReflectionException
     */
    public function beforeMethodCall(ManifestInterface $manifest, string $methodName, array $params, mixed &$returnValue)
    {
        $this->applyWrappers($manifest, $methodName, $params, BeforeMethod::class);
    }

    /**
     * @param ManifestInterface $manifest
     * @param string            $methodName
     * @param array             $params
     * @param mixed             $returnedValue
     * @param mixed             $returnValue
     *
     * @throws ReflectionException
     */
    public function afterMethodCall(ManifestInterface $manifest, string $methodName, array $params, mixed $returnedValue, mixed &$returnValue)
    {
        $this->applyWrappers($manifest, $methodName, $params, AfterMethod::class);
    }

    public function supports(ManifestInterface $manifest, string $methodName, array $params): bool
    {
        return true;
    }

    /**
     * @param ManifestInterface $manifest
     * @param string            $methodName
     * @param array             $params
     * @param string            $attributeClass
     *
     * @throws ReflectionException
     */
    private function applyWrappers(ManifestInterface $manifest, string $methodName, array $params, string $attributeClass)
    {
        $class = new ReflectionObject($manifest);
        foreach ($class->getMethods() as $method) {
            $attributes = AttributesUtil::getFunctionAttributes($method, $attributeClass, true);
            if (0 === count($attributes)) {
                continue;
            }

            $isWrapper = false;
            foreach ($attributes as $attribute) {
                /** @var BeforeMethod|AfterMethod $attr */
                $attr = $attribute->newInstance();
                if (!in_array($this->env, $attr->envs())) {
                    continue;
                }
                if ($methodName === $attr->methodName()) {
                    $isWrapper = true;
                }
            }
            if (!$isWrapper) {
                continue;
            }

            if (!$method->isPublic()) {
                throw new LogicException(
                    sprintf(
                        'Method "%s::%s()" uses attribute "%s" and therefore must be public',
                        $class->getName(),
                        $method->getName(),
                        $attributeClass
                    )
                );
            }

            $originalMethod = $class->getMethod($methodName);
            if (!ReflectionUtil::sameSignature($originalMethod, $method)) {
                throw new LogicException(
                    sprintf(
                        'Method "%s::%s()" uses attribute "%s" to wrap method "%s()", but has another signature',
                        $class->getName(),
                        $method->getName(),
                        $attributeClass,
                        $methodName
                    )
                );
            }

            $method->invoke($manifest, ...$params);
        }
    }
}
