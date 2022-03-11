<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Bridge\Symfony\DependencyInjection;

use EonX\EasyPagination\Interfaces\PaginationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('easy_pagination');

        $treeBuilder->getRootNode()
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

        return $treeBuilder;
    }
}
