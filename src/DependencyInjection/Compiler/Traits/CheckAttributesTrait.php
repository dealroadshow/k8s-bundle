<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler\Traits;

use Dealroadshow\Bundle\K8SBundle\EnvManagement\Attribute\EnabledForContainerParameter;
use Dealroadshow\Bundle\K8SBundle\EnvManagement\Attribute\EnabledForEnvs;
use Dealroadshow\Bundle\K8SBundle\Util\AttributesUtil;
use Symfony\Component\DependencyInjection\ContainerBuilder;

trait CheckAttributesTrait
{
    protected function enabledForCurrentEnv(\ReflectionClass $class, string $currentEnv): bool
    {
        $attributes = AttributesUtil::getClassAttributes($class, EnabledForEnvs::class);
        while ($parentClass = $class->getParentClass()) {
            $parentAttributes = AttributesUtil::getClassAttributes($parentClass, EnabledForEnvs::class);
            $attributes = [...$attributes, ...$parentAttributes];
            $class = $parentClass;
        }
        $enabled = 0 === count($attributes); // Enabled by default if there is no attributes
        $attributes = array_reverse($attributes);
        foreach ($attributes as $attribute) {
            /** @var EnabledForEnvs $attr */
            $attr = $attribute->newInstance();
            $enabled = in_array($currentEnv, $attr->envs());
        }

        return $enabled;
    }

    protected function enabledForContainerParameter(\ReflectionClass $class, ContainerBuilder $container): bool
    {
        /** @var EnabledForContainerParameter|null $attribute */
        $attribute = AttributesUtil::fromClassOrParents($class, EnabledForContainerParameter::class);
        if (null === $attribute) {
            return true;
        }

        if (!$container->hasParameter($attribute->parameter)) {
            throw new \RuntimeException(
                sprintf(
                    'Parameter "%s", used in PHP attribute "%s", does not exist in Symfony service container',
                    $attribute->parameter,
                    EnabledForContainerParameter::class
                )
            );
        }
        $parameter = $container->resolveEnvPlaceholders($container->getParameter($attribute->parameter));
        if (!is_bool($parameter)) {
            throw new \RuntimeException(
                sprintf(
                    'Parameter "%s", used in PHP attribute "%s", must contain a bool value, %s is given',
                    $attribute->parameter,
                    EnabledForContainerParameter::class,
                    gettype($parameter)
                )
            );
        }

        return $parameter;
    }
}
