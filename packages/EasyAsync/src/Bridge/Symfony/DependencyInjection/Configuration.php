<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Bridge\Symfony\DependencyInjection;

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
                ->arrayNode('batch')
                    ->addDefaultsIfNotSet()
                    ->children()
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
