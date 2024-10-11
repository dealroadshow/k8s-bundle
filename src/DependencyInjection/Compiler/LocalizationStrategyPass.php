<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler;

use Dealroadshow\K8S\Framework\App\Integration\ExternalEnvSourcesRegistry;
use Dealroadshow\K8S\Framework\App\Integration\Localization\AbstractLocalizationStrategy;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class LocalizationStrategyPass implements CompilerPassInterface
{
    public const LOCALIZATION_STRATEGY_TAG = 'dealroadshow_k8s.configuration.localization.strategy';

    public function process(ContainerBuilder $container): void
    {
        $ids = $container->findTaggedServiceIds(self::LOCALIZATION_STRATEGY_TAG);
        foreach (array_keys($ids) as $id) {
            $strategyDefinition = $container->getDefinition($id);
            if (is_subclass_of($strategyDefinition->getClass(), AbstractLocalizationStrategy::class)) {
                $strategyDefinition->addMethodCall(
                    'setEnvSourcesRegistry',
                    [new Reference(ExternalEnvSourcesRegistry::class)]
                );
            }
        }
    }
}
