<?php

namespace Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler;

use Dealroadshow\K8S\Framework\Middleware\ContainerImageMiddlewareInterface;
use Dealroadshow\K8S\Framework\Middleware\ManifestMethodPrefixMiddlewareInterface;
use Dealroadshow\K8S\Framework\Middleware\ManifestMethodSuffixMiddlewareInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MiddlewarePass implements CompilerPassInterface
{
    const IMAGE_MIDDLEWARE_TAG           = 'dealroadshow_k8s.middleware.container_image';
    const MANIFEST_PREFIX_MIDDLEWARE_TAG = 'dealroadshow_k8s.middleware.prefix';
    const MANIFEST_SUFFIX_MIDDLEWARE_TAG = 'dealroadshow_k8s.middleware.suffix';

    public function process(ContainerBuilder $container)
    {
        $container
            ->registerForAutoconfiguration(ContainerImageMiddlewareInterface::class)
            ->addTag(self::IMAGE_MIDDLEWARE_TAG);

        $container
            ->registerForAutoconfiguration(ManifestMethodPrefixMiddlewareInterface::class)
            ->addTag(self::MANIFEST_PREFIX_MIDDLEWARE_TAG);

        $container
            ->registerForAutoconfiguration(ManifestMethodSuffixMiddlewareInterface::class)
            ->addTag(self::MANIFEST_SUFFIX_MIDDLEWARE_TAG);
    }
}
