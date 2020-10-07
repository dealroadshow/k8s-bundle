<?php

namespace Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Dealroadshow\K8S\Framework\ResourceMaker\ResourceMakerInterface;

class ResourceMakersPass implements CompilerPassInterface
{
    const RESOURCE_MAKER_TAG = 'dealroadshow_k8s.resource_maker';

    public function process(ContainerBuilder $container)
    {
        $container->registerForAutoconfiguration(ResourceMakerInterface::class)
            ->addTag(ResourceMakersPass::RESOURCE_MAKER_TAG);
    }
}
