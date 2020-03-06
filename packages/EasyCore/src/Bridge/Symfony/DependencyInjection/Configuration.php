<?php
declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('easy_core');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('api_platform')
                    ->children()
                        ->booleanNode('custom_pagination')->defaultValue(true)->end()
                    ->end()
                ->end()
                ->arrayNode('security')
                    ->children()
                        ->arrayNode('permissions_targets')
                            ->scalarPrototype()->end()
                            ->beforeNormalization()->castToArray()->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
