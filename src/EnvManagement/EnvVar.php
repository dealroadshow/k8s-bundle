<?php

namespace Dealroadshow\Bundle\K8SBundle\EnvManagement;

use Closure;
use ReflectionObject;
use RuntimeException;

class EnvVar
{
    private static Closure|null $getEnvClosure = null;

    public static function get(string $name): mixed
    {
        $closure = self::getClosure();

        return $closure($name);
    }

    private static function getClosure(): Closure
    {
        if (null === self::$getEnvClosure) {
            $container = DIContainerRegistry::get();
            $class = new ReflectionObject($container);
            if (!$class->hasMethod('getEnv')) {
                throw new RuntimeException(
                    sprintf('DI container class "%s" does not have method "getEnv()"', $class->getName())
                );
            }
            $method = $class->getMethod('getEnv');
            $method->setAccessible(true);
            self::$getEnvClosure = $method->getClosure($container);
        }

        return self::$getEnvClosure;
    }
}
