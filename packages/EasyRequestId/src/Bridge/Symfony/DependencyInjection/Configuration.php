<?php

declare(strict_types=1);

namespace EonX\EasyRequestId\Bridge\Symfony\DependencyInjection;

use EonX\EasyRequestId\Interfaces\ResolverInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('easy_request_id');

        $treeBuilder->getRootNode()
            ->children()
                ->booleanNode('default_resolver')->defaultTrue()->end()
                ->scalarNode('default_request_id_header')
                    ->defaultValue(ResolverInterface::DEFAULT_REQUEST_ID_HEADER)
                ->end()
                ->scalarNode('default_correlation_id_header')
                    ->defaultValue(ResolverInterface::DEFAULT_CORRELATION_ID_HEADER)
                ->end()
            ->end();

        return $treeBuilder;
    }
}
