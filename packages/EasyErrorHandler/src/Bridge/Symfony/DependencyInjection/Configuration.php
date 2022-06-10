<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Symfony\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('easy_error_handler');

        $treeBuilder->getRootNode()
            ->children()
                //Bugsnag
                ->booleanNode('bugsnag_enabled')->defaultTrue()->end()
                ->integerNode('bugsnag_threshold')->defaultNull()->end()
                ->arrayNode('bugsnag_handled_exceptions')
                    ->beforeNormalization()->castToArray()->end()
                    ->scalarPrototype()->end()
                ->end()
                ->arrayNode('bugsnag_ignored_exceptions')
                    ->beforeNormalization()->castToArray()->end()
                    ->scalarPrototype()->end()
                ->end()
                ->arrayNode('logger_exception_log_levels')
                    ->beforeNormalization()->castToArray()->end()
                    ->integerPrototype()->end()
                ->end()
                ->arrayNode('logger_ignored_exceptions')
                    ->beforeNormalization()->castToArray()->end()
                    ->scalarPrototype()->end()
                ->end()
                ->arrayNode('ignored_exceptions')
                    ->beforeNormalization()->castToArray()->end()
                    ->scalarPrototype()->end()
                ->end()
                ->booleanNode('verbose')->defaultFalse()->end()
                ->booleanNode('override_api_platform_listener')->defaultTrue()->end()
                ->booleanNode('use_default_builders')->defaultTrue()->end()
                ->booleanNode('use_default_reporters')->defaultTrue()->end()
                ->scalarNode('translation_domain')->defaultValue('messages')->end()
                ->arrayNode('response')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('code')->defaultValue('code')->end()
                        ->scalarNode('exception')->defaultValue('exception')->end()
                        ->arrayNode('extended_exception_keys')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('class')->defaultValue('class')->end()
                                ->scalarNode('file')->defaultValue('file')->end()
                                ->scalarNode('line')->defaultValue('line')->end()
                                ->scalarNode('message')->defaultValue('message')->end()
                                ->scalarNode('trace')->defaultValue('trace')->end()
                            ->end()
                        ->end()
                        ->scalarNode('message')->defaultValue('message')->end()
                        ->scalarNode('sub_code')->defaultValue('sub_code')->end()
                        ->scalarNode('time')->defaultValue('time')->end()
                        ->scalarNode('violations')->defaultValue('violations')->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
