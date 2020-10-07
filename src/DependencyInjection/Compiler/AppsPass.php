<?php

namespace Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AppsPass implements CompilerPassInterface
{
    const APP_TAG = 'dealroadshow_k8s.app';

    public function process(ContainerBuilder $container)
    {
        $env = $container->getParameter('kernel.environment');
        $ids = $container->findTaggedServiceIds(self::APP_TAG);
        foreach ($ids as $id => $tags) {
            $definition = $container->getDefinition($id);
            $definition->addMethodCall('setEnv', [$env]);
        }
    }
}
