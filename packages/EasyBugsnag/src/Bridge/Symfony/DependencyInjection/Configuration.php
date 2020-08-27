<?php

declare(strict_types=1);

namespace EonX\EasyBugsnag\Bridge\Symfony\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('easy_bugsnag');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('api_key')->isRequired()->end()
                ->arrayNode('doctrine_dbal')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultFalse()->end()
                        ->scalarNode('connections')
                            ->defaultValue('default')
                            ->info('Doctrine DBAL connections name pipe separated')
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
