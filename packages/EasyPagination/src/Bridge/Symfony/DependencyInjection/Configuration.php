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
                        ->scalarNode('per_page_attribute')->defaultValue(PaginationInterface::DEFAULT_PER_PAGE_ATTRIBUTE)->end()
                        ->integerNode('per_page_default')->defaultValue(PaginationInterface::DEFAULT_PER_PAGE)->end()
                    ->end()
                ->end()
                ->booleanNode('use_default_resolver')
                    ->defaultTrue()
                    ->info('Resolve pagination from request by default')
                ->end()
                ->scalarNode('resolver')
                    ->setDeprecated()
                    ->defaultValue('in_query')
                    ->info('Define which resolver to use. Available resolvers are: in_query, array_in_query.')
                    ->validate()
                        ->ifNotInArray(['array_in_query', 'in_query'])
                        ->thenInvalid('Invalid resolver %s')
                    ->end()
                ->end()
                ->scalarNode('array_in_query_attr')
                    ->setDeprecated()
                    ->defaultValue('page')
                    ->info(
                        'This config is used to resolve the pagination data when it is expected in the query
                        parameters of the request as an array. This config is the name of the query parameter containing
                        the pagination data array.'
                    )
                    ->example(
                        'For this config as "page", the resolver will look in the query for:
                        "<your-url>?page[<number_attr>]=1&page[<size_attr>]=15"'
                    )
                ->end()
                ->arrayNode('start_size')
                    ->setDeprecated(
                        'The child node "%node%" at path "%path%" is deprecated. Use pagination instead.'
                    )
                    ->info(
                        'This config contains the names of the attributes to use to resolve the start_size
                        pagination data, and also their default values if not set on the given request.'
                    )
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('start_attribute')->defaultValue('page')->end()
                        ->integerNode('start_default')->defaultValue(1)->end()
                        ->scalarNode('size_attribute')->defaultValue('perPage')->end()
                        ->integerNode('size_default')->defaultValue(15)->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
