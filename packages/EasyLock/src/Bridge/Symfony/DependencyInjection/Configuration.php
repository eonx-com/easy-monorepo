<?php

declare(strict_types=1);

namespace EonX\EasyLock\Bridge\Symfony\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('easy_lock');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('connection')->defaultValue('doctrine.dbal.default_connection')->end()
            ->end();

        return $treeBuilder;
    }
}
