<?php
declare(strict_types=1);

use EonX\EasyPagination\Interfaces\PaginationInterface;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

return static function (DefinitionConfigurator $definition) {
    $definition->rootNode()
        ->children()
            ->arrayNode('pagination')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('page_attribute')->defaultValue(PaginationInterface::DEFAULT_PAGE_ATTRIBUTE)->end()
                    ->integerNode('page_default')->defaultValue(PaginationInterface::DEFAULT_PAGE)->end()
                    ->scalarNode('per_page_attribute')
                        ->defaultValue(PaginationInterface::DEFAULT_PER_PAGE_ATTRIBUTE)
                        ->end()
                    ->integerNode('per_page_default')->defaultValue(PaginationInterface::DEFAULT_PER_PAGE)->end()
                ->end()
            ->end()
            ->booleanNode('use_default_resolver')
                ->defaultTrue()
                ->info('Resolve pagination from request by default')
            ->end()
        ->end();
};
