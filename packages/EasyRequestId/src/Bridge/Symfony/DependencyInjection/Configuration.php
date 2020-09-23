<?php

declare(strict_types=1);

namespace EonX\EasyRequestId\Bridge\Symfony\DependencyInjection;

use EonX\EasyRequestId\Interfaces\RequestIdKeysAwareInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('easy_request_id');

        $treeBuilder->getRootNode()
            ->children()
                // Keys
                ->scalarNode('correlation_id_key')
                    ->defaultValue(RequestIdKeysAwareInterface::KEY_CORRELATION_ID)
                ->end()
                ->scalarNode('request_id_key')
                    ->defaultValue(RequestIdKeysAwareInterface::KEY_REQUEST_ID)
                ->end()
                // Defaults
                ->booleanNode('default_resolver')->defaultTrue()->end()
                ->scalarNode('default_request_id_header')
                    ->defaultValue(RequestIdKeysAwareInterface::KEY_REQUEST_ID)
                ->end()
                ->scalarNode('default_correlation_id_header')
                    ->defaultValue(RequestIdKeysAwareInterface::KEY_CORRELATION_ID)
                ->end()
                // Bridges
                ->booleanNode('easy_bugsnag')->defaultTrue()->end()
                ->booleanNode('easy_error_handler')->defaultTrue()->end()
                ->booleanNode('easy_logging')->defaultTrue()->end()
                ->booleanNode('easy_webhook')->defaultTrue()->end()
            ->end();

        return $treeBuilder;
    }
}
