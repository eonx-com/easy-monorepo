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
                ->booleanNode('easy_doctrine_subscriber_enabled')
                    ->defaultValue(true)
                    ->info('Whether easy-doctrine subscriber should handle events.')
                ->end()
                ->scalarNode('table_name')
                    ->defaultValue('easy_activity_logs')
                    ->info('The name for table with logs. Defaults to "easy_activity_logs".')
                ->end()
                ->arrayNode('disallowed_properties')
                    ->defaultValue([])
                    ->prototype('scalar')->end()
                    ->info('Property names disallowed to be stored in store.')
                ->end()
                ->arrayNode('subjects')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('type')
                                ->info('Subject type. Defaults to short class name of subject.')
                            ->end()
                            ->arrayNode('disallowed_properties')
                                ->defaultValue([])
                                ->info('Property names disallowed to be stored in store.')
                                ->prototype('scalar')->end()
                            ->end()
                            ->arrayNode('allowed_properties')
                                ->defaultValue([])
                                ->info('Property names allowed to be stored in store.')
                                ->prototype('scalar')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
