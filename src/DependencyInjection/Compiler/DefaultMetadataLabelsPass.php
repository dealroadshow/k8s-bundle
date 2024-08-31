<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler;

use Dealroadshow\Bundle\K8SBundle\EventListener\DefaultMetadataLabelsSubscriber;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DefaultMetadataLabelsPass implements CompilerPassInterface
{
    public const PARAM = 'dealroadshow_k8s.default_metadata_labels';

    public function process(ContainerBuilder $container): void
    {
        if ($container->getParameter(self::PARAM)) {
            $container->removeDefinition(DefaultMetadataLabelsSubscriber::class);
        }
    }
}
