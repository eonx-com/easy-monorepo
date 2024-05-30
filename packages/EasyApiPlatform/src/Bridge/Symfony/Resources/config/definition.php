<?php
declare(strict_types=1);

use EonX\EasyApiPlatform\Bridge\EasyErrorHandler\Interface\ApiPlatformErrorResponseBuilderInterface;
use EonX\EasyBugsnag\Bridge\Symfony\EasyBugsnagSymfonyBundle;
use EonX\EasyErrorHandler\Bridge\Symfony\EasyErrorHandlerSymfonyBundle;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;

return static function (DefinitionConfigurator $definition) {
    $rootNode = $definition->rootNode();
    $rootNode
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
            ->end()
            ->arrayNode('custom_paginator')
                ->canBeDisabled()
            ->end()
        ->end();

    if (ContainerBuilder::willBeAvailable(
        'eonx-com/easy-error-handler',
        EasyErrorHandlerSymfonyBundle::class,
        []
    )) {
        $rootNode
            ->children()
                ->arrayNode('easy_error_handler')
                    ->canBeDisabled()
                    ->children()
                        ->arrayNode('custom_serializer_exceptions')
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
                    ->end()
                ->end()
            ->end();
    }

    if (ContainerBuilder::willBeAvailable(
        'eonx-com/easy-bugsnag',
        EasyBugsnagSymfonyBundle::class,
        []
    )) {
        $rootNode
            ->children()
                ->arrayNode('easy_bugsnag')
                    ->canBeDisabled()
                ->end()
            ->end();
    }
};
