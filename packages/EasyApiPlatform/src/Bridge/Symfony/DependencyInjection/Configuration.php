<?php

declare(strict_types=1);

namespace EonX\EasyApiPlatform\Bridge\Symfony\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('easy_api_platform');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('advanced_search_filter')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('iri_fields')
                            ->defaultValue([])
                            ->info('Fields that could be passed as IRI')
                            ->prototype('scalar')
                        ->end()
                    ->end()
                ->end()
                ->booleanNode('custom_paginator_enabled')->defaultTrue()->end()
            ->end();

        return $treeBuilder;
    }
}
