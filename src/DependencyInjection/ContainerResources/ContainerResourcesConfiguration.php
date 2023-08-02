<?php

namespace Dealroadshow\Bundle\K8SBundle\DependencyInjection\ContainerResources;

use Dealroadshow\K8S\Framework\Core\Container\Resources\CPU;
use Dealroadshow\K8S\Framework\Core\Container\Resources\Memory;
use Symfony\Component\Config\Definition\Builder\ScalarNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

readonly class ContainerResourcesConfiguration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('resources');
        $root = $treeBuilder->getRootNode();

        $root
            ->children()
                ->append($this->cpuNode())
                ->append($this->memoryNode('memory'))
                ->append($this->memoryNode('storage'))
            ->end();

        return $treeBuilder;
    }

    private function cpuNode(): ScalarNodeDefinition
    {
        $treeBuilder = new TreeBuilder('cpu', 'scalar');

        /** @var ScalarNodeDefinition $root */
        $root = $treeBuilder->getRootNode();
        $root
            ->cannotBeEmpty()
            ->beforeNormalization()
                ->ifString()
                ->then(function(string $cpu) {
                    try {
                        CPU::fromString($cpu);
                    } catch (\InvalidArgumentException $e) {
                        throw new InvalidConfigurationException($e->getMessage());
                    }
                })
            ->end();

        return $root;
    }

    private function memoryNode(string $name): ScalarNodeDefinition
    {
        $treeBuilder = new TreeBuilder($name, 'scalar');

        /** @var ScalarNodeDefinition $root */
        $root = $treeBuilder->getRootNode();
        $root
            ->cannotBeEmpty()
            ->beforeNormalization()
                ->ifString()
                ->then(function(string $memory) {
                    try {
                        Memory::fromString($memory);
                    } catch (\InvalidArgumentException $e) {
                        throw new InvalidConfigurationException($e->getMessage());
                    }
                })
            ->end();

        return $root;
    }
}