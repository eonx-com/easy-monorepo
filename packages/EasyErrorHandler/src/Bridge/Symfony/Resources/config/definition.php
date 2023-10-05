<?php
declare(strict_types=1);

use EonX\EasyErrorHandler\Bridge\Symfony\Interfaces\ApiPlatformErrorResponseBuilderInterface;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

return static function (DefinitionConfigurator $definition) {
    $defaultLocale = 'en';
    $defaultErrorCodesCategorySize = 100;

    $definition->rootNode()
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
            ->booleanNode('bugsnag_ignore_validation_errors')
                ->setDeprecated(
                    'eonx-com/easy-error-handler',
                    '5.8',
                    'This option is deprecated and will be removed in 6.0.' .
                    ' Use "bugsnag_ignore_api_platform_builder_errors" instead.'
                )
                ->defaultTrue()
            ->end()
            ->booleanNode('bugsnag_ignore_api_platform_builder_errors')
                ->info('If true, errors handled by ' . ApiPlatformErrorResponseBuilderInterface::class
                    . ' will be ignored')
                ->defaultTrue()
            ->end()
            ->booleanNode('transform_validation_errors')
                ->setDeprecated(
                    'eonx-com/easy-error-handler',
                    '5.8',
                    'This option is deprecated and will be removed in 6.0.' .
                    ' Use "use_api_platform_builders" instead.'
                )
                ->defaultTrue()
            ->end()
            ->booleanNode('use_api_platform_builders')->defaultTrue()->end()
            ->arrayNode('api_platform_custom_serializer_exceptions')
                ->info('Custom serializer exceptions to be handled by '
                    . ApiPlatformErrorResponseBuilderInterface::class)
                ->arrayPrototype()
                    ->children()
                        ->scalarNode('class')->isRequired()->end()
                        ->scalarNode('message_pattern')->isRequired()->end()
                        ->scalarNode('violation_message')->isRequired()->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('logger_exception_log_levels')
                ->useAttributeAsKey('class')
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
            ->booleanNode('report_retryable_exception_attempts')->defaultFalse()->end()
            ->booleanNode('verbose')->defaultFalse()->end()
            ->booleanNode('override_api_platform_listener')
                ->setDeprecated(
                    'eonx-com/easy-error-handler',
                    '5.8',
                    'This option is deprecated and will be removed in 6.0.' .
                    ' Use "use_api_platform_builders" instead.'
                )
                ->defaultTrue()
            ->end()
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
                ->addDefaultsIfNotSet()
                ->children()
                    ->booleanNode('enabled')->defaultFalse()->end()
                    ->scalarNode('locale')->defaultValue($defaultLocale)->end()
                ->end()
            ->end()
            ->scalarNode('error_codes_interface')->defaultNull()->end()
            ->scalarNode('error_codes_category_size')->defaultValue($defaultErrorCodesCategorySize)->end()
        ->end();
};
