<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Interfaces\Batch;

interface BatchStoreInterface
{
    public function find(string $batchId): ?BatchInterface;

    public function store(BatchInterface $batch): BatchInterface;

    public function updateForItem(BatchInterface $batch, BatchItemInterface $batchItem): BatchInterface;
}
