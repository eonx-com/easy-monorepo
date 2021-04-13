<?php

declare(strict_types=1);

namespace EonX\EasyRandom\Bridge\Symfony\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('easy_random');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('uuid_v4_generator')
                    ->defaultNull()
                    ->info('Service id of the UUID V4 generator to use')
                ->end()
            ->end();

        return $treeBuilder;
    }
}
