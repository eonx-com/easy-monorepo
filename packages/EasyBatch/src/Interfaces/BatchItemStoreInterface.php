<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Interfaces;

interface BatchItemStoreInterface extends BatchObjectStoreInterface
{
    /**
     * @var string
     */
    public const DEFAULT_BATCH_ITEM_TABLE = 'easy_batch_items';
}
