<?php
declare(strict_types=1);

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

return static function (DefinitionConfigurator $definition) {
    $definition->rootNode()
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
            ->booleanNode('custom_paginator_enabled')
                ->defaultTrue()
            ->end()
        ->end();
};
