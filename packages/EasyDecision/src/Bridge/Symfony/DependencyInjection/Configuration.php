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
                ->arrayNode('type_mapping')
                    ->prototype('scalar')
                    ->validate()
                        ->ifTrue(static function ($class) {
                            return \class_exists($class) === false;
                        })
                        ->thenInvalid('Class %s does not exist.')
                    ->end()
                    ->end()
                    ->info(
                        'Decision type mapping to be used by ' .
                        '\EonX\EasyDecision\Interfaces\DecisionFactoryInterface::createByName',
                    )
                ->end()
            ->end();

        return $treeBuilder;
    }
}
