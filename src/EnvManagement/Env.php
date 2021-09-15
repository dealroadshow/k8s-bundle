<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\EnvManagement;

use Closure;
use ReflectionObject;
use RuntimeException;

class Env
{
    public const DEV = 'dev';
    public const QA = 'qa';
    public const PRODUCTION = 'prod';
    public const STAGING = 'staging';
    public const TEST = 'test';

    // Pseudo env used in some classes like EnvAwareContainerMaker
    public const DEFAULT = 'default';

    private static Closure|null $getEnvClosure = null;

    public static function var(string $name): string
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
                throw new RuntimeException(sprintf('DI container class "%s" does not have method "getEnv()"', $class->getName()));
            }
            $method = $class->getMethod('getEnv');
            $method->setAccessible(true);
            self::$getEnvClosure = $method->getClosure($container);
        }

        return self::$getEnvClosure;
    }
}
