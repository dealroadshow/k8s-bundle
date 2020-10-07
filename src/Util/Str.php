<?php

namespace Dealroadshow\Bundle\K8SBundle\Util;

class Str
{
    public static function asClassName(string $str): string
    {
        $str = ucwords($str, " \t\r\n\f\v-_");

        return preg_replace('/[\s\-_]+/', '', $str);
    }

    public static function withoutSuffix(string $str, string $suffix)
    {
        return str_ends_with($str, $suffix)
            ? substr($str, 0, -strlen($suffix))
            : $str;
    }
}
