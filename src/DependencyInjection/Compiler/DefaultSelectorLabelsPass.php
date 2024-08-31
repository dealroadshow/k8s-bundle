<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler;

use Dealroadshow\Bundle\K8SBundle\EventListener\DefaultSelectorLabelsSubscriber;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DefaultSelectorLabelsPass implements CompilerPassInterface
{
    public const PARAM = 'dealroadshow_k8s.set_default_selector_labels';

    public function process(ContainerBuilder $container): void
    {
        if ($container->getParameter(self::PARAM)) {
            $container->removeDefinition(DefaultSelectorLabelsSubscriber::class);
        }
    }
}