<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\EnvManagement;

class Env
{
    public const string DEV = 'dev';
    public const string QA = 'qa';
    public const string PRODUCTION = 'prod';
    public const string STAGING = 'staging';
    public const string TEST = 'test';

    // Pseudo env used in some classes like EnvAwareContainerMaker
    public const string DEFAULT = 'default';

    private static \Closure|null $getEnvClosure = null;

    public static function var(string $name): string
    {
        $closure = self::getClosure();

        return $closure($name);
    }

    private static function getClosure(): \Closure
    {
        if (null === self::$getEnvClosure) {
            $container = DIContainerRegistry::get();
            $class = new \ReflectionObject($container);
            if (!$class->hasMethod('getEnv')) {
                throw new \RuntimeException(sprintf('DI container class "%s" does not have method "getEnv()"', $class->getName()));
            }
            $method = $class->getMethod('getEnv');
            self::$getEnvClosure = $method->getClosure($container);
        }

        return self::$getEnvClosure;
    }
}
