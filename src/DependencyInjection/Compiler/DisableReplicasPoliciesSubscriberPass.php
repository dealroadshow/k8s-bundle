<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler;

use Dealroadshow\Bundle\K8SBundle\EventListener\ReplicasPoliciesSubscriber;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

readonly class DisableReplicasPoliciesSubscriberPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->getParameter('dealroadshow_k8s.deployment.replicas.policies.enabled')) {
            $container->removeDefinition(ReplicasPoliciesSubscriber::class);
        }
    }
}
