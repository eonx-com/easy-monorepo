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
                        ->booleanNode('simple_data_persister_enabled')->defaultValue(true)->end()
                        ->arrayNode('open_api_normalizer')
                            ->children()
                                ->booleanNode('enabled')->defaultFalse()->end()
                                ->scalarNode('base_uri')->defaultValue('http://localhost/')->end()
                                ->scalarNode('contexts_file')->defaultNull()->end()
                                ->booleanNode('default_processors_enabled')->defaultFalse()->end()
                                ->arrayNode('processors')
                                    ->beforeNormalization()->castToArray()->end()
                                    ->defaultValue([])
                                    ->prototype('scalar')->end()
                                ->end()
                                ->arrayNode('doc_path_processor')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->arrayNode('endpoints_remove_params')
                                            ->beforeNormalization()->castToArray()->end()
                                            ->defaultValue([])
                                            ->prototype('scalar')->end()
                                        ->end()
                                        ->arrayNode('endpoints_remove_body')
                                            ->beforeNormalization()->castToArray()->end()
                                            ->defaultValue([])
                                            ->prototype('scalar')->end()
                                        ->end()
                                        ->arrayNode('endpoints_remove_response')
                                            ->beforeNormalization()->castToArray()->end()
                                            ->defaultValue([])
                                            ->prototype('scalar')->end()
                                        ->end()
                                        ->arrayNode('skip_method_names')
                                            ->beforeNormalization()->castToArray()->end()
                                            ->defaultValue([])
                                            ->prototype('scalar')->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('search')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultFalse()->end()
                        ->scalarNode('elasticsearch_host')->defaultValue('elasticsearch:9200')->end()
                    ->end()
                ->end()
                ->arrayNode('profiler_storage')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultFalse()->end()
                    ->end()
                ->end()
                ->arrayNode('trim_strings')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultFalse()->end()
                        ->arrayNode('except')
                            ->scalarPrototype()->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
