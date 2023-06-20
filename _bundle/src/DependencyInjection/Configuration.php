<?php

namespace AnyContent\Backend\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('any_content_backend');

        // https://stackoverflow.com/questions/34323106/symfony-config-treebuilder
        $treeBuilder
            ->getRootNode()
                ->children()
                ->arrayNode('connections')
                    ->prototype('array')
                        ->children()
                        ->scalarNode('name')->isRequired()->end()
                        ->enumNode('type')->values(['mysql', 'recordfiles', 'recordsfile', 'contentarchive'])->end()
                        ->scalarNode('path')->end()
                    ->end()
                ->end()
            ->end();

        // ToDo: Think about key repositories, to restrict repositories and role to restrict connections

        return $treeBuilder;
    }
}
