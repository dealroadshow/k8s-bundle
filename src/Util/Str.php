<?php

namespace Dealroadshow\Bundle\K8SBundle\Util;

use InvalidArgumentException;
use ReflectionObject;

class Str
{
    public static function asClassName(string $str): string
    {
        $str = ucwords($str, " \t\r\n\f\v-_");

        return preg_replace('/[\s\-_]+/', '', $str);
    }

    public static function withoutSuffix(string $str, string $suffix): string
    {
        return str_ends_with($str, $suffix)
            ? substr($str, 0, -strlen($suffix))
            : $str;
    }

    public static function withSuffix(string $className, string $suffix): string
    {
        return Str::withoutSuffix($className, $suffix).$suffix;
    }

    public static function asDirName(string $str, string $suffix = null): string
    {
        $className = self::asClassName($str);

        return $suffix ? self::withoutSuffix($className, $suffix) : $className;
    }

    public static function asNamespace(object $object): string
    {
        $reflection = new ReflectionObject($object);
        $namespace = $reflection->getNamespaceName();

        return trim($namespace, '\\');
    }

    public static function asDir(object $object): string
    {
        $reflection = new ReflectionObject($object);

        return dirname($reflection->getFileName());
    }

    public static function asDNSSubdomain(string $str): string
    {
        $camel2dash = strtolower(
            preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1-', $str)
        );
        $valid = preg_replace('/([^\w\-]|[_])+/', '', $camel2dash);

        if (0 === strlen($valid)) {
            throw new InvalidArgumentException(
                sprintf('String "%s" cannot be converted DNS subdomain representation', $str)
            );
        }
        if (253 < strlen($valid)) {
            throw new InvalidArgumentException(
                sprintf(
                    'String "%s" DNS subdomain representation is too long (must be less than 253 characters)',
                    $str
                )
            );
        }

        return $valid;
    }

    public static function isValidDNSSubdomain(string $str): bool
    {
        return 253 > strlen($str) && 0 !== preg_match('/^[a-z0-9]+[a-z0-9\-.]+[a-z0-9]$/', $str);
    }
}
