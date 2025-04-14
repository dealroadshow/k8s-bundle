<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler;

use Dealroadshow\Bundle\K8SBundle\EventListener\ResourcesPoliciesSubscriber;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

readonly class DisableResourcesPoliciesSubscriberPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->getParameter('dealroadshow_k8s.container.resources.policies.enabled')) {
            $container->removeDefinition(ResourcesPoliciesSubscriber::class);
        }
    }
}
