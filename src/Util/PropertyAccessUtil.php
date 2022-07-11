<?php

namespace Dealroadshow\Bundle\K8SBundle\Util;

class PropertyAccessUtil
{
    public static function getPropertyValue(object $object, string $propertyName): mixed
    {
        return (fn () => $this->$propertyName)->call($object);
    }

    public static function setPropertyValue(object $object, string $propertyName, mixed $propertyValue): void
    {
        $fn = function (mixed $newValue) use ($propertyName): void {
            $this->$propertyName = $newValue;
        };
        $fn->call($object, $propertyValue);
    }
}
