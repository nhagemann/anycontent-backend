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
                        ->enumNode('type')->values(['recordsfile', 'recordfiles', 'contentarchive', 'mysql'])->isRequired()->end()
                        ->scalarNode('content_file')->end()
                        ->scalarNode('config_file')->end()
                        ->scalarNode('cmdl_file')->end()
                        ->scalarNode('content_path')->end()
                        ->scalarNode('cmdl_path')->end()
                        ->scalarNode('data_path')->end()
                        ->scalarNode('db_host')->defaultValue('127.0.0.1')->end()
                        ->scalarNode('db_name')->defaultValue('anycontent')->end()
                        ->scalarNode('db_user')->defaultValue('anycontent')->end()
                        ->scalarNode('db_password')->end()
                        ->scalarNode('db_port')->defaultValue('3306')->end()
                        ->scalarNode('files_path')->end()
                    ->end()
                ->end()
            ->end();

        $treeBuilder
            ->getRootNode()
            ->children()
            ->arrayNode('formelements')
                ->prototype('array')
                    ->children()
                    ->scalarNode('type')->isRequired()->end()
                    ->scalarNode('class')->isRequired()->end()
                    ->scalarNode('custom_type')->end()
                ->end()
            ->end()
            ->end();

        // ToDo: Think about key repositories, to restrict repositories and role to restrict connections

        return $treeBuilder;
    }
}
