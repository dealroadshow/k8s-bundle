<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\EventListener\Traits;

use Dealroadshow\Bundle\K8SBundle\EnvManagement\Attribute\AfterMethod;
use Dealroadshow\Bundle\K8SBundle\EnvManagement\Attribute\BeforeMethod;
use Dealroadshow\Bundle\K8SBundle\Util\AttributesUtil;
use Dealroadshow\K8S\Framework\Core\ProxyableInterface;
use Dealroadshow\K8S\Framework\Util\ReflectionUtil;
use Dealroadshow\Proximity\ProxyInterface;

trait ApplyWrappersTrait
{
    /**
     * @throws \ReflectionException
     */
    private function applyWrappers(ProxyableInterface $proxyable, string $methodName, array $params, string $attributeClass, mixed &$returnValue): void
    {
        $class = new \ReflectionObject($proxyable);
        if ($class->implementsInterface(ProxyInterface::class)) {
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
                throw new \LogicException(sprintf('Method "%s::%s()" uses attribute "%s" and therefore must be public', $class->getName(), $method->getName(), $attributeClass));
            }

            $originalMethod = $class->getMethod($methodName);
            if (!ReflectionUtil::sameSignature($originalMethod, $method)) {
                throw new \LogicException(sprintf('Method "%s::%s()" uses attribute "%s" to wrap method "%s()", but has another signature', $class->getName(), $method->getName(), $attributeClass, $methodName));
            }

            $result = $method->invoke($proxyable, ...$params);
            if ($replacesReturnValue) {
                $returnValue = $result;
            }
        }
    }
}
