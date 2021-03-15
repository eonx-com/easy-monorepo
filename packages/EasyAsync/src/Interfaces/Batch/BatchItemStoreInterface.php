<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Interfaces\Batch;

interface BatchItemStoreInterface
{
    /**
     * @var string
     */
    public const DEFAULT_TABLE = 'easy_async_batch_items';

    public function store(BatchItemInterface $batchItem): BatchItemInterface;
}
