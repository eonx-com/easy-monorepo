<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Interfaces;

interface BatchStoreInterface extends BatchObjectStoreInterface
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

    /**
     * @throws \EonX\EasyBatch\Exceptions\BatchNotFoundException
     */
    public function findForUpdate(string $batchId): BatchInterface;

    public function finishUpdate(): void;

    public function startUpdate(): void;

    public function store(BatchInterface $batch): BatchInterface;
}
