<?php

namespace AnyContent\Backend\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('anycontent');

        $treeBuilder->getRootNode()
            ->children()
            ->arrayNode('repositories')
            ->children()
            ->scalarNode('name')->end()
            ->scalarNode('path')->end()
            ->end()
            ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}