<?php
declare(strict_types=1);

use EonX\EasyBatch\Common\ValueObject\Batch;
use EonX\EasyBatch\Common\ValueObject\BatchItem;
use EonX\EasyBatch\Common\ValueObject\BatchObjectInterface;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

return static function (DefinitionConfigurator $definition) {
    $definition->rootNode()
        ->children()
            ->scalarNode('batch_class')->defaultValue(Batch::class)->end()
            ->scalarNode('batch_table')->defaultValue('easy_batches')->end()
            ->scalarNode('batch_item_class')->defaultValue(BatchItem::class)->end()
            ->integerNode('batch_item_per_page')->defaultValue(15)->end()
            ->scalarNode('batch_item_table')->defaultValue('easy_batch_items')->end()
            ->scalarNode('date_time_format')->defaultValue('Y-m-d H:i:s.u')->end()
            ->floatNode('lock_ttl')->defaultNull()->end()
        ->end();
};
