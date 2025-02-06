<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler;

use Dealroadshow\K8S\Framework\Runtime\ManifestStatusResolverStrategyInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RuntimeStatusResolverStrategiesPass implements CompilerPassInterface
{
    public const TAG = 'dealroadshow_k8s.runtime_status_resolver.strategy';

    public function process(ContainerBuilder $container): void
    {
        $container->registerForAutoconfiguration(ManifestStatusResolverStrategyInterface::class)
            ->addTag(self::TAG);
    }
}
