<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler;

use Dealroadshow\Bundle\K8SBundle\EventListener\AutoSetResourcesSubscriber;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AutoSetResourcesPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $autoSetResources = $container->getParameter('dealroadshow_k8s.auto_set_resources');
        if (!$autoSetResources) {
            $container->removeDefinition(AutoSetResourcesSubscriber::class);
        }
    }
}
