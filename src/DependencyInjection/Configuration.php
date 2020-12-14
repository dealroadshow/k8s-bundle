<?php

namespace Dealroadshow\Bundle\K8SBundle\DependencyInjection;

use Closure;
use Dealroadshow\K8S\Framework\App\AppInterface;
use ReflectionClass;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('dealroadshow_k8s');
        $root = $treeBuilder->getRootNode();

        $root
            ->children()
                ->scalarNode('code_dir')->defaultNull()->end()
                ->scalarNode('namespace_prefix')->defaultValue('App\\K8S\\')->end()
                ->scalarNode('manifests_dir')->defaultNull()->end()
                ->arrayNode('apps')
                    ->addDefaultsIfNotSet()
                    ->useAttributeAsKey('alias')
                    ->arrayPrototype()
                        ->ignoreExtraKeys()
                        ->canBeDisabled()
                        ->children()
                            ->scalarNode('class')
                                ->cannotBeEmpty()
                                ->cannotBeOverwritten()
                                ->beforeNormalization()
                                    ->ifString()
                                    ->then(Closure::fromCallable([$this, 'validClassName']))
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('filterManifests')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('byTags')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->arrayNode('include')
                                    ->scalarPrototype()->end()
                                ->end()
                                ->arrayNode('exclude')
                                    ->scalarPrototype()->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }

    private function validClassName(string $class): string
    {
        if (!class_exists($class)) {
            throw new InvalidConfigurationException(
                sprintf('Class "%s" does not exist', $class)
            );
        }

        $reflection = new ReflectionClass($class);
        if (!$reflection->implementsInterface(AppInterface::class)) {
            throw new InvalidConfigurationException(
                sprintf('App  class "%s" must implement AppInterface', $class)
            );
        }

        return $class;
    }
}
