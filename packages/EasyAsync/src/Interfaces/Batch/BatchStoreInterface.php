<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Interfaces\Batch;

/**
 * @deprecated since 3.3, will be removed in 4.0. Use eonx-com/easy-batch instead.
 */
interface BatchStoreInterface
{
    /**
     * @var string
     */
    public const DATETIME_FORMAT = 'Y-m-d H:i:s';

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

    public function update(BatchInterface $batch): BatchInterface;
}
