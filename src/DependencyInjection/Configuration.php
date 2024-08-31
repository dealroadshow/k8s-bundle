<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\DependencyInjection;

use Dealroadshow\Bundle\K8SBundle\DependencyInjection\ContainerResources\ContainerResourcesConfiguration;
use Dealroadshow\K8S\Framework\App\AppInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

readonly class Configuration implements ConfigurationInterface
{
    public const PARAM_SET_DEFAULT_SELECTOR_LABELS = 'set_default_selector_labels';
    public const PARAM_SET_DEFAULT_METADATA_LABELS = 'set_default_metadata_labels';
    public const PARAM_SET_DEFAULT_SERVICE_SELECTOR = 'set_default_service_selector';

    private ContainerResourcesConfiguration $resourcesConfiguration;
    private Processor $processor;

    public function __construct()
    {
        $this->resourcesConfiguration = new ContainerResourcesConfiguration();
        $this->processor = new Processor();
    }

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('dealroadshow_k8s');
        $root = $treeBuilder->getRootNode();

        $root
            ->children()
                ->append($this->appsNode())
                ->scalarNode('code_dir')->defaultNull()->end()
                ->scalarNode('namespace_prefix')->defaultValue('App\\K8S\\')->end()
                ->scalarNode('manifests_dir')->defaultNull()->end()
                ->booleanNode('auto_set_resources')
                    ->defaultFalse()
                ->end()
                ->booleanNode('auto_set_replicas')
                    ->defaultFalse()
                ->end()
                ->booleanNode(self::PARAM_SET_DEFAULT_SELECTOR_LABELS)
                    ->defaultTrue()
                ->end()
                ->booleanNode(self::PARAM_SET_DEFAULT_METADATA_LABELS)
                    ->defaultTrue()
                ->end()
                ->booleanNode(self::PARAM_SET_DEFAULT_SERVICE_SELECTOR)
                    ->defaultTrue()
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

    public function appsNode(): ArrayNodeDefinition
    {
        $treeBuilder = new TreeBuilder('apps');
        $node = $treeBuilder->getRootNode();

        $node
            ->useAttributeAsKey('alias')
            ->arrayPrototype()
                ->ignoreExtraKeys(false)
                ->canBeDisabled()
                ->children()
                    ->scalarNode('class')
                        ->cannotBeEmpty()
                        ->cannotBeOverwritten()
                        ->beforeNormalization()
                            ->ifString()
                            ->then(static::validClassName(...))
                        ->end()
                    ->end()
                    ->arrayNode('manifests')
                        ->useAttributeAsKey('name')
                        ->arrayPrototype()
                            ->ignoreExtraKeys(false)
                            ->children()
                                ->booleanNode('virtual')
                                    ->defaultFalse()
                                ->end()
                                ->integerNode('replicas')
                                ->end()
                                ->arrayNode('resources')
                                    ->children()
                                        ->variableNode('requests')
                                            ->validate()
                                                ->ifArray()
                                                ->then($this->validResourcesNodeValue(...))
                                            ->end()
                                        ->end()
                                        ->variableNode('limits')
                                            ->validate()
                                                ->ifArray()
                                                ->then($this->validResourcesNodeValue(...))
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $node;
    }

    private static function validClassName(string $class): string
    {
        if (!class_exists($class)) {
            throw new InvalidConfigurationException(sprintf('Class "%s" does not exist', $class));
        }

        $reflection = new \ReflectionClass($class);
        if (!$reflection->implementsInterface(AppInterface::class)) {
            throw new InvalidConfigurationException(sprintf('App  class "%s" must implement AppInterface', $class));
        }

        return $class;
    }

    private function validResourcesNodeValue(array $nodeValue): array
    {
        return $this->processor->processConfiguration($this->resourcesConfiguration, [$nodeValue]);
    }
}
