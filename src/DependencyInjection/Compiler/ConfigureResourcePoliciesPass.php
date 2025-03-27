<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler;

use Dealroadshow\Bundle\K8SBundle\EnvManagement\Container\ResourcePolicyConfiguratorInterface;
use Dealroadshow\Bundle\K8SBundle\EnvManagement\Container\ResourcePolicyRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ConfigureResourcePoliciesPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(ResourcePolicyConfiguratorInterface::class)) {
            return;
        }

        $definition = $container->findDefinition(ResourcePolicyConfiguratorInterface::class);
        $definition->addMethodCall('configure', [new Reference(ResourcePolicyRegistry::class)]);
    }
}
