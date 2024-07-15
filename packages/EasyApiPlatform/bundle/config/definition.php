<?php
declare(strict_types=1);

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

return static function (DefinitionConfigurator $definition) {
    $definition->rootNode()
        ->children()
            ->arrayNode('advanced_search_filter')
                ->info('Configures options for the `\EonX\EasyApiPlatform\Filter\AdvancedSearchFilter` class.')
                ->addDefaultsIfNotSet()
                ->children()
                    ->arrayNode('iri_fields')
                        ->defaultValue([])
                        ->info('An array of fields to be treated as IRIs.')
                        ->prototype('scalar')
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->booleanNode('custom_paginator_enabled')
                ->defaultTrue()
            ->end()
        ->end();
};
