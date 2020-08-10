<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('easy_core');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('api_platform')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('custom_pagination_enabled')->defaultValue(true)->end()
                        ->booleanNode('no_properties_api_resource_enabled')->defaultValue(true)->end()
                        ->booleanNode('simple_data_persister_enabled')->defaultValue(true)->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
