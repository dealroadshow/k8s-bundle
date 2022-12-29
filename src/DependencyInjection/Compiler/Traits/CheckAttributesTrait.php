<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler\Traits;

use Dealroadshow\Bundle\K8SBundle\EnvManagement\Attribute\EnabledForEnvs;
use Dealroadshow\Bundle\K8SBundle\EnvManagement\Attribute\DisabledForEnvVar;
use Dealroadshow\Bundle\K8SBundle\Util\AttributesUtil;

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

    protected function enabledForEnvVar(\ReflectionClass $class): bool
    {
        do {
            $attributes = AttributesUtil::getClassAttributes($class, DisabledForEnvVar::class);
            $class = $class->getParentClass();
        } while (0 === count($attributes) && false !== $class);

        foreach ($attributes as $attribute) {
            /** @var DisabledForEnvVar $attr */
            $attr = $attribute->newInstance();
            $envVar = getenv($attr->envVarName);
            if (is_string($envVar) && 'false' === mb_strtolower($envVar)) {
                $envVar = false;
            }

            return !$envVar;
        }

        return true;
    }
}
