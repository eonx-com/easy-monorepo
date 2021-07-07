<?php

declare(strict_types=1);

namespace EonX\EasyHttpClient\Bridge\Symfony\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('easy_http_client');

        $treeBuilder->getRootNode()
            ->children()
                ->booleanNode('decorate_default_client')->defaultFalse()->end()
                ->booleanNode('decorate_easy_webhook_client')->defaultFalse()->end()
                ->booleanNode('easy_bugsnag_enabled')->defaultTrue()->end()
                ->booleanNode('psr_logger_enabled')->defaultTrue()->end()
            ->end();

        return $treeBuilder;
    }
}
