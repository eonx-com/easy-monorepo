<?php
declare(strict_types=1);

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

return static function (DefinitionConfigurator $definition) {
    $definition->rootNode()
        ->children()
            ->arrayNode('pagination')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('page_attribute')->defaultValue('page')->end()
                    ->integerNode('page_default')->defaultValue(1)->end()
                    ->scalarNode('per_page_attribute')->defaultValue('perPage')->end()
                    ->integerNode('per_page_default')->defaultValue(15)->end()
                ->end()
            ->end()
            ->booleanNode('use_default_resolver')
                ->defaultTrue()
                ->info('Resolve pagination from request by default')
            ->end()
        ->end();
};
