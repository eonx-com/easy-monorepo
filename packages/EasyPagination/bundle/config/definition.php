<?php
declare(strict_types=1);

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

return static function (DefinitionConfigurator $definition) {
    $definition->rootNode()
        ->children()
            ->arrayNode('pagination')
                ->addDefaultsIfNotSet()
                ->children()
                    ->stringNode('page_attribute')->defaultValue('page')->end()
                    ->integerNode('page_default')->defaultValue(1)->end()
                    ->stringNode('per_page_attribute')->defaultValue('perPage')->end()
                    ->integerNode('per_page_default')->defaultValue(15)->end()
                    ->integerNode('per_page_max')
                        ->defaultNull()
                        ->min(1)
                        ->info('The largest page size a client can request, null for no limit')
                    ->end()
                ->end()
            ->end()
            ->booleanNode('use_default_resolver')
                ->defaultTrue()
                ->info('Resolve pagination from request by default')
            ->end()
        ->end();
};
