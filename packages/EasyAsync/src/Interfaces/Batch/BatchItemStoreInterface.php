<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Interfaces\Batch;

/**
 * @deprecated since 3.3, will be removed in 4.0. Use eonx-com/easy-batch instead.
 */
interface BatchItemStoreInterface
{
    /**
     * @var string
     */
    public const DEFAULT_TABLE = 'easy_async_batch_items';

    public function store(BatchItemInterface $batchItem): BatchItemInterface;
}
