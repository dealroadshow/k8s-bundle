<?php

namespace Dealroadshow\Bundle\K8SBundle\Util;

use LogicException;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionFunctionAbstract;

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

    /**
     * @param ReflectionFunctionAbstract $func
     * @param string                     $attributeClass
     * @param bool                       $multipleAllowed
     *
     * @return ReflectionAttribute[]
     */
    public static function getFunctionAttributes(ReflectionFunctionAbstract $func, string $attributeClass, bool $multipleAllowed = false): array
    {
        $attributes = $func->getAttributes($attributeClass);
        if (count($attributes) > 1 && !$multipleAllowed) {
            throw new LogicException(
                sprintf(
                    'Method/function "%s", defined in file "%s", has multiple attributes "%s", but only one such attribute per function is allowed',
                    $func->getName(),
                    $func->getFileName(),
                    $attributeClass
                )
            );
        }

        return $attributes;
    }
}
