<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Bridge\Symfony\DependencyInjection;

use EonX\EasyAsync\Batch\Batch;
use EonX\EasyAsync\Interfaces\Batch\BatchItemStoreInterface;
use EonX\EasyAsync\Interfaces\Batch\BatchStoreInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('easy_async');

        $treeBuilder->getRootNode()
            ->children()
                // Current config
                ->arrayNode('messenger_worker')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('stop_on_messages_limit')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('enabled')->defaultFalse()->end()
                                ->integerNode('min_messages')->end()
                                ->integerNode('max_messages')->defaultNull()->end()
                            ->end()
                        ->end()
                        ->arrayNode('stop_on_time_limit')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('enabled')->defaultFalse()->end()
                                ->integerNode('min_time')->end()
                                ->integerNode('max_time')->defaultNull()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()

                // Deprecated config, will be removed in 4.0.
                ->arrayNode('batch')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('default_batch_class')->defaultValue(Batch::class)->end()
                        ->scalarNode('batches_table')->defaultValue(BatchStoreInterface::DEFAULT_TABLE)->end()
                        ->scalarNode('batch_items_table')->defaultValue(BatchItemStoreInterface::DEFAULT_TABLE)->end()
                        ->arrayNode('messenger_buses')
                            ->beforeNormalization()->castToArray()->end()
                            ->scalarPrototype()->end()
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('implementation')->defaultValue('doctrine')->end()
                ->scalarNode('jobs_table')->defaultValue('easy_async_jobs')->end()
                ->scalarNode('job_logs_table')->defaultValue('easy_async_job_logs')->end()
            ->end();

        return $treeBuilder;
    }
}
