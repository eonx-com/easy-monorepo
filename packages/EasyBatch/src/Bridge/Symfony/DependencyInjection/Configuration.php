<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Bridge\Symfony\DependencyInjection;

use EonX\EasyBatch\Interfaces\BatchItemRepositoryInterface;
use EonX\EasyBatch\Interfaces\BatchObjectInterface;
use EonX\EasyBatch\Interfaces\BatchObjectManagerInterface;
use EonX\EasyBatch\Interfaces\BatchRepositoryInterface;
use EonX\EasyBatch\Objects\Batch;
use EonX\EasyBatch\Objects\BatchItem;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('easy_batch');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('batch_class')->defaultValue(Batch::class)->end()
                ->scalarNode('batch_table')
                    ->defaultValue(BatchRepositoryInterface::DEFAULT_TABLE)
                ->end()
                ->scalarNode('batch_item_class')->defaultValue(BatchItem::class)->end()
                ->integerNode('batch_item_per_page')
                    ->defaultValue(BatchObjectManagerInterface::DEFAULT_BATCH_ITEMS_PER_PAGE)
                ->end()
                ->scalarNode('batch_item_table')
                    ->defaultValue(BatchItemRepositoryInterface::DEFAULT_TABLE)
                ->end()
                ->scalarNode('date_time_format')->defaultValue(BatchObjectInterface::DATETIME_FORMAT)->end()
                ->floatNode('lock_ttl')->defaultNull()->end()
            ->end();

        return $treeBuilder;
    }
}
