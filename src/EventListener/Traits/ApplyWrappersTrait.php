<?php

namespace Dealroadshow\Bundle\K8SBundle\EventListener\Traits;

use Dealroadshow\Bundle\K8SBundle\EnvManagement\Attribute\AfterMethod;
use Dealroadshow\Bundle\K8SBundle\EnvManagement\Attribute\BeforeMethod;
use Dealroadshow\Bundle\K8SBundle\Util\AttributesUtil;
use Dealroadshow\K8S\Framework\Core\ManifestInterface;
use Dealroadshow\K8S\Framework\Util\ReflectionUtil;
use LogicException;
use ProxyManager\Proxy\AccessInterceptorInterface;
use ReflectionException;
use ReflectionObject;

trait ApplyWrappersTrait
{
    /**
     * @param ManifestInterface $manifest
     * @param string            $methodName
     * @param array             $params
     * @param string            $attributeClass
     * @param mixed             $returnValue
     *
     * @throws ReflectionException
     */
    private function applyWrappers(ManifestInterface $manifest, string $methodName, array $params, string $attributeClass, mixed & $returnValue): void
    {
        $class = new ReflectionObject($manifest);
        if ($class->implementsInterface(AccessInterceptorInterface::class)) {
            $class = $class->getParentClass();
        }
        foreach ($class->getMethods() as $method) {
            if ($method->getName() === $methodName) {
                continue;
            }
            $attributes = AttributesUtil::getFunctionAttributes($method, $attributeClass, true);
            if (0 === count($attributes)) {
                continue;
            }

            $isWrapper = false;
            $replacesReturnValue = false;
            foreach ($attributes as $attribute) {
                /** @var BeforeMethod|AfterMethod $attr */
                $attr = $attribute->newInstance();
                if (!in_array($this->env, $attr->envs())) {
                    continue;
                }
                if ($methodName === $attr->methodName()) {
                    $isWrapper = true;
                    $replacesReturnValue = $attr->replacesReturnValue();
                    break;
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

            $result = $method->invoke($manifest, ...$params);
            if ($replacesReturnValue) {
                $returnValue = $result;
            }
        }
    }
}
