<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler;

use Dealroadshow\K8S\Framework\ResourceMaker\ResourceMakerInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ResourceMakersPass implements CompilerPassInterface
{
    public const RESOURCE_MAKER_TAG = 'dealroadshow_k8s.resource_maker';

    public function process(ContainerBuilder $container): void
    {
        $container->registerForAutoconfiguration(ResourceMakerInterface::class)
            ->addTag(ResourceMakersPass::RESOURCE_MAKER_TAG);
    }
}
