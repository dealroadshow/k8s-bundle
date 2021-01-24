<?php

namespace Dealroadshow\Bundle\K8SBundle\Util;

use LogicException;
use ReflectionAttribute;
use ReflectionClass;

class AttributesUtil
{
    /**
     * @param ReflectionClass $class
     * @param string          $attributeClass
     * @param bool            $multipleAllowed
     *
     * @return ReflectionAttribute[]
     */
    public static function getClassAttributes(ReflectionClass $class, string $attributeClass, bool $multipleAllowed = false): array
    {
        $attributes = $class->getAttributes($attributeClass);
        if (count($attributes) > 1 && !$multipleAllowed) {
            throw new LogicException(
                sprintf(
                    'Class "%s" has multiple attributes "%s", but only one such attribute per class is allowed',
                    $class->getName(),
                    $attributeClass
                )
            );
        }

        return $attributes;
    }
}
