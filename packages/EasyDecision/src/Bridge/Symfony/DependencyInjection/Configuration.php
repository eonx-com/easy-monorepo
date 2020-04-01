<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Bridge\Symfony\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('easy_decision');

        $treeBuilder->getRootNode()
            ->children()
                ->booleanNode('use_expression_language')
                    ->defaultValue(true)
                    ->info('If set to true, the expression language will be set on all decisions automatically')
                ->end()
            ->end();

        return $treeBuilder;
    }
}
