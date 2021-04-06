<?php

namespace Dealroadshow\Bundle\K8SBundle\EventListener\Traits;

use Dealroadshow\Bundle\K8SBundle\EnvManagement\Attribute\AfterMethod;
use Dealroadshow\Bundle\K8SBundle\EnvManagement\Attribute\BeforeMethod;
use Dealroadshow\Bundle\K8SBundle\Util\AttributesUtil;
use Dealroadshow\K8S\Framework\Core\ManifestInterface;
use Dealroadshow\K8S\Framework\Util\ReflectionUtil;
use LogicException;
use ReflectionException;
use ReflectionObject;

trait ApplyWrappersTrait
{
    /**
     * @param ManifestInterface $manifest
     * @param string            $methodName
     * @param array             $params
     * @param string            $attributeClass
     *
     * @throws ReflectionException
     */
    private function applyWrappers(ManifestInterface $manifest, string $methodName, array $params, string $attributeClass): void
    {
        $class = new ReflectionObject($manifest);
        foreach ($class->getMethods() as $method) {
            if ($method->getName() === $methodName) {
                continue;
            }
            $attributes = AttributesUtil::getFunctionAttributes($method, $attributeClass, true);
            if (0 === count($attributes)) {
                continue;
            }

            $isWrapper = false;
            foreach ($attributes as $attribute) {
                /** @var BeforeMethod|AfterMethod $attr */
                $attr = $attribute->newInstance();
                if (!static::shouldApply($this->env, $attr->enabledForEnvs(), $attr->disabledForEnvs())) {
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

    private static function shouldApply(string $env, array $enabledForEnvs, array $disabledForEnvs): bool
    {
        if (!empty($enabledForEnvs) && !empty($disabledForEnvs)) {
            throw new \LogicException('Only one of enabledForEnvs and disabledForEnvs can be specified');
        }

        return (!empty($enabledForEnvs) && in_array($env, $enabledForEnvs))
            || (!empty($disabledForEnvs && !in_array($env, $disabledForEnvs)));
    }
}
