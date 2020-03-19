<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Symfony\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('easy_security');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('context_service_id')->defaultValue('App\Security\Interfaces\ContextInterface')->end()
                ->scalarNode('token_decoder')->defaultValue('chain')->end()
                ->arrayNode('permissions_locations')
                    ->scalarPrototype()->end()
                    ->beforeNormalization()->castToArray()->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
