<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\EnvManagement;

use Symfony\Component\DependencyInjection\ContainerInterface;

class DIContainerRegistry
{
    private static ContainerInterface $container;

    public static function set(ContainerInterface $container): void
    {
        self::$container = $container;
    }

    public static function get(): ContainerInterface
    {
        return self::$container;
    }
}
