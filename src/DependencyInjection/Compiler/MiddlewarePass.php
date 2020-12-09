<?php

namespace Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler;

use Dealroadshow\K8S\Framework\Middleware\ContainerImageMiddlewareInterface;
use Dealroadshow\K8S\Framework\Middleware\ManifestMethodMiddlewareInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MiddlewarePass implements CompilerPassInterface
{
    const IMAGE_MIDDLEWARE_TAG = 'dealroadshow_k8s.middleware.container_image';
    const MANIFEST_MIDDLEWARE_TAG = 'dealroadshow_k8s.middleware.manifest_method';

    public function process(ContainerBuilder $container)
    {
        $container->registerForAutoconfiguration(ContainerImageMiddlewareInterface::class)
            ->addTag(self::IMAGE_MIDDLEWARE_TAG);

        $container->registerForAutoconfiguration(ManifestMethodMiddlewareInterface::class)
            ->addTag(self::MANIFEST_MIDDLEWARE_TAG);
    }
}
