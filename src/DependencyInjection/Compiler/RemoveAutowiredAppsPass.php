<?php

namespace Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RemoveAutowiredAppsPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        foreach ($container->findTaggedServiceIds(AppsPass::APP_TAG) as $id => $tags) {
            if (!class_exists($id)) {
                continue;
            }
            $container->removeDefinition($id);
        }
    }
}