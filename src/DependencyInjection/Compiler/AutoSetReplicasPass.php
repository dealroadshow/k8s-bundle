<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler;

use Dealroadshow\Bundle\K8SBundle\EventListener\AutoSetReplicasSubscriber;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AutoSetReplicasPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $autoSetReplicas = $container->getParameter('dealroadshow_k8s.auto_set_replicas');
        if (!$autoSetReplicas) {
            $container->removeDefinition(AutoSetReplicasSubscriber::class);
        }
    }
}
