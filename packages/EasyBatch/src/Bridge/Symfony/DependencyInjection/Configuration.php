<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Bridge\Symfony\DependencyInjection;

use EonX\EasyBatch\Interfaces\BatchItemStoreInterface;
use EonX\EasyBatch\Interfaces\BatchObjectInterface;
use EonX\EasyBatch\Interfaces\BatchStoreInterface;
use EonX\EasyBatch\Objects\Batch;
use EonX\EasyBatch\Objects\BatchItem;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('easy_async');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('batch_class')->defaultValue(Batch::class)->end()
                ->scalarNode('batch_table')
                    ->defaultValue(BatchStoreInterface::DEFAULT_BATCH_TABLE)
                ->end()
                ->scalarNode('batch_item_class')->defaultValue(BatchItem::class)->end()
                ->scalarNode('batch_item_table')
                    ->defaultValue(BatchItemStoreInterface::DEFAULT_BATCH_ITEM_TABLE)
                ->end()
                ->scalarNode('date_time_format')->defaultValue(BatchObjectInterface::DATETIME_FORMAT)->end()
            ->end();

        return $treeBuilder;
    }
}
