<?php

namespace Dealroadshow\Bundle\K8SBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('dealroadshow_k8s');
        $root = $treeBuilder->getRootNode();

        $root
            ->children()
                ->scalarNode('code_dir')->defaultValue('K8S')->end()
                ->scalarNode('root_namespace')->defaultValue('App\\')->end()
                ->scalarNode('manifests_dir')->defaultNull()->end()
            ->end();

        return $treeBuilder;
    }
}
