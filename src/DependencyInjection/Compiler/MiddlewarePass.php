<?php

namespace Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler;

use Dealroadshow\K8S\Framework\Middleware\ContainerImageMiddlewareInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MiddlewarePass implements CompilerPassInterface
{
    const CONTAINER_IMAGE_TAG = 'dealroadshow_k8s.container_image_middleware';

    public function process(ContainerBuilder $container)
    {
        $container->registerForAutoconfiguration(ContainerImageMiddlewareInterface::class)
            ->addTag(self::CONTAINER_IMAGE_TAG);
    }
}
