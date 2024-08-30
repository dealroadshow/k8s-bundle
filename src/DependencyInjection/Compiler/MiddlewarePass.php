<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler;

use Dealroadshow\K8S\Framework\Middleware\ContainerImageMiddlewareInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MiddlewarePass implements CompilerPassInterface
{
    public const string IMAGE_MIDDLEWARE_TAG = 'dealroadshow_k8s.middleware.container_image';

    public function process(ContainerBuilder $container): void
    {
        $container
            ->registerForAutoconfiguration(ContainerImageMiddlewareInterface::class)
            ->addTag(self::IMAGE_MIDDLEWARE_TAG);
    }
}
