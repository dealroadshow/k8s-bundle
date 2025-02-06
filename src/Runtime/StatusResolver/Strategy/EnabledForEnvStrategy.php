<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\Runtime\StatusResolver\Strategy;

use Dealroadshow\Bundle\K8SBundle\EnvManagement\Attribute\EnabledForEnvs;
use Dealroadshow\Bundle\K8SBundle\Util\AttributesUtil;
use Dealroadshow\K8S\Framework\Runtime\ManifestStatusResolverStrategyInterface;

readonly class EnabledForEnvStrategy implements ManifestStatusResolverStrategyInterface
{
    public function __construct(private string $env)
    {
    }

    public function isClassEnabled(\ReflectionClass $class): bool
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
            $enabled = in_array($this->env, $attr->envs());
        }

        return $enabled;
    }
}
