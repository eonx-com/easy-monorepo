<?php
declare(strict_types=1);

use Monolog\Logger;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\HttpFoundation\Exception\RequestExceptionInterface;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return static function (DefinitionConfigurator $definition) {
    $definition->rootNode()
        ->children()
            ->arrayNode('easy_bugsnag')
                ->canBeDisabled()
                ->children()
                    ->integerNode('threshold')->defaultNull()->end()
                    ->arrayNode('handled_exceptions')
                        ->beforeNormalization()->castToArray()->end()
                        ->scalarPrototype()->end()
                    ->end()
                    ->arrayNode('ignored_exceptions')
                        ->defaultValue([
                            HttpExceptionInterface::class,
                            RequestExceptionInterface::class,
                        ])
                        ->beforeNormalization()->castToArray()->end()
                        ->scalarPrototype()->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('logger')
                ->addDefaultsIfNotSet()
                ->children()
                    ->arrayNode('exception_log_levels')
                        ->useAttributeAsKey('class')
                        ->beforeNormalization()->castToArray()->end()
                        ->integerPrototype()->end()
                        ->defaultValue([
                            HttpExceptionInterface::class => Logger::DEBUG,
                            RequestExceptionInterface::class => Logger::DEBUG,
                        ])
                    ->end()
                    ->arrayNode('ignored_exceptions')
                        ->beforeNormalization()->castToArray()->end()
                        ->scalarPrototype()->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('ignored_exceptions')
                ->beforeNormalization()->castToArray()->end()
                ->scalarPrototype()->end()
            ->end()
            ->booleanNode('report_retryable_exception_attempts')->defaultFalse()->end()
            ->booleanNode('skip_reported_exceptions')->defaultFalse()->end()
            ->booleanNode('verbose')->defaultFalse()->end()
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
            ->arrayNode('translate_internal_error_messages')
                ->canBeEnabled()
                ->children()
                    ->scalarNode('locale')->defaultValue('en')->end()
                ->end()
            ->end()
            ->scalarNode('error_codes_interface')->defaultNull()->end()
            ->scalarNode('error_codes_category_size')->defaultValue(100)->end()
        ->end();
};