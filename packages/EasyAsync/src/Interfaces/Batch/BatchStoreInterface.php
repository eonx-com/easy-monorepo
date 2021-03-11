<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Interfaces\Batch;

interface BatchStoreInterface
{
    /**
     * @var string
     */
    public const DEFAULT_TABLE = 'easy_async_batches';

    public function cancelUpdate(): void;

    public function find(string $batchId): ?BatchInterface;

    /**
     * @throws \EonX\EasyAsync\Exceptions\Batch\BatchNotFoundException
     */
    public function findForUpdate(string $batchId): BatchInterface;

    public function finishUpdate(): void;

    public function startUpdate(): void;

    public function store(BatchInterface $batch): BatchInterface;

    public function storeForUpdate(BatchInterface $batch): BatchInterface;
}
