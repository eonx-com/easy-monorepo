<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Interfaces\Batch;

interface BatchStoreInterface
{
    /**
     * @var string
     */
    public const DEFAULT_TABLE = 'easy_async_batches';

    public function find(string $batchId): ?BatchInterface;

    public function store(BatchInterface $batch): BatchInterface;

    public function updateForItem(BatchInterface $batch, BatchItemInterface $batchItem): BatchInterface;
}
