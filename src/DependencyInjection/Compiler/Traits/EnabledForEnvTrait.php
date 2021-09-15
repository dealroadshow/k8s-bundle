<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler\Traits;

use Dealroadshow\Bundle\K8SBundle\EnvManagement\Attribute\EnabledForEnvs;
use Dealroadshow\Bundle\K8SBundle\Util\AttributesUtil;
use ReflectionClass;

trait EnabledForEnvTrait
{
    public function enabledForCurrentEnv(ReflectionClass $class, string $currentEnv): bool
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
}
