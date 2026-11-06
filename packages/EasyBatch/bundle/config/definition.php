<?php
declare(strict_types=1);

use EonX\EasyBatch\Common\ValueObject\Batch;
use EonX\EasyBatch\Common\ValueObject\BatchItem;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

return static function (DefinitionConfigurator $definition) {
    $definition->rootNode()
        ->children()
            ->stringNode('batch_class')->defaultValue(Batch::class)->end()
            ->stringNode('batch_table')->defaultValue('easy_batches')->end()
            ->stringNode('batch_item_class')->defaultValue(BatchItem::class)->end()
            ->integerNode('batch_item_per_page')->defaultValue(15)->end()
            ->stringNode('batch_item_table')->defaultValue('easy_batch_items')->end()
            ->stringNode('date_time_format')->defaultValue('Y-m-d H:i:s.u')->end()
            ->floatNode('lock_ttl')->defaultNull()->end()
            ->arrayNode('signing_secret')
                ->info(
                    'Secret used to sign batch item messages, defaults to the "kernel.secret" parameter. '
                    . 'List several to rotate it: the first one signs, all of them verify.'
                )
                ->acceptAndWrap(['string'])
                ->performNoDeepMerging()
                ->scalarPrototype()->cannotBeEmpty()->end()
            ->end()
            ->booleanNode('allow_unsigned_messages')
                ->defaultTrue()
                ->info(
                    'Accept stored batch item messages without a signature, written before signing existed. '
                    . 'Set to false once no such batch item is left to process. '
                    . 'A future major will default this to false.'
                )
            ->end()
        ->end();
};
