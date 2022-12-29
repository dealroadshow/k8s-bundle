<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\Util;

class AttributesUtil
{
    /**
     * @return \ReflectionAttribute[]
     */
    public static function getClassAttributes(\ReflectionClass $class, string $attributeClass, bool $multipleAllowed = false): array
    {
        $attributes = $class->getAttributes($attributeClass);
        if (count($attributes) > 1 && !$multipleAllowed) {
            throw new \LogicException(sprintf('Class "%s" has multiple attributes "%s", but only one such attribute per class is allowed', $class->getName(), $attributeClass));
        }

        return $attributes;
    }

    /**
     * @return \ReflectionAttribute[]
     */
    public static function getFunctionAttributes(\ReflectionFunctionAbstract $func, string $attributeClass, bool $multipleAllowed = false): array
    {
        $attributes = $func->getAttributes($attributeClass);
        if (count($attributes) > 1 && !$multipleAllowed) {
            throw new \LogicException(sprintf('Method/function "%s", defined in file "%s", has multiple attributes "%s", but only one such attribute per function is allowed', $func->getName(), $func->getFileName(), $attributeClass));
        }

        return $attributes;
    }

    public static function fromClass(\ReflectionClass $class, string $attributeClass): object|null
    {
        $reflections = self::getClassAttributes($class, $attributeClass);
        if (0 === count($reflections)) {
            return null;
        }

        return $reflections[0]->newInstance();
    }

    public static function fromClassOrParents(\ReflectionClass $class, string $attributeClass): object|null
    {
        $attribute = self::fromClass($class, $attributeClass);
        if ($attribute) {
            return $attribute;
        }
        $parent = $class->getParentClass();
        if (false === $parent) {
            return null;
        }

        return self::fromClassOrParents($parent, $attributeClass);
    }
}
