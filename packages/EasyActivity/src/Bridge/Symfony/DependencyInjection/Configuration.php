<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Bridge\Symfony\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('easy_activity');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('disallowed_properties')
                    ->defaultValue([])
                    ->prototype('scalar')
                    ->end()
                    ->info(
                        'Field names disallowed to be stored in store.'
                    )
                ->end()
                ->arrayNode('subjects')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('type')->end()
                            ->arrayNode('disallowed_properties')
                                ->defaultValue([])
                                ->prototype('scalar')->end()
                            ->end()
                            ->arrayNode('allowed_properties')
                                ->defaultValue([])
                                ->prototype('scalar')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
