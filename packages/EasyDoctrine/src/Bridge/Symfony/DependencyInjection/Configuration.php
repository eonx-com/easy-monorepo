<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Bridge\Symfony\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('easy_doctrine');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('entities')
                    ->defaultValue([])
                    ->prototype('scalar')->end()
                ->end()
                ->booleanNode('easy_error_handler_enabled')
                    ->defaultValue(true)
                ->end()
            ->end();

        return $treeBuilder;
    }
}
