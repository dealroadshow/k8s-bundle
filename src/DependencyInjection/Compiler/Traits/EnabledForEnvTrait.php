<?php

namespace Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler\Traits;

use Dealroadshow\Bundle\K8SBundle\EnvManagement\Attribute\EnabledForEnvs;
use ReflectionClass;

trait EnabledForEnvTrait
{
    public function enabledForCurrentEnv(ReflectionClass $class, string $currentEnv): bool
    {
        $attributes = $class->getAttributes(EnabledForEnvs::class);
        $enabled = 0 === count($attributes); // Enabled by default if there is no attributes
        foreach ($attributes as $attribute) {
            /** @var EnabledForEnvs $attr */
            $attr = $attribute->newInstance();
            if (in_array($currentEnv, $attr->envs())) {
                $enabled = true;
                break;
            }
        }

        return $enabled;
    }
}
