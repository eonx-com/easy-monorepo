<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Interfaces;

interface BatchStoreInterface extends BatchObjectStoreInterface
{
    /**
     * @var string
     */
    public const DEFAULT_BATCH_TABLE = 'easy_batches';

    public function cancelUpdate(): void;

    public function finishUpdate(): void;

    public function lockForUpdate($batchId): void;

    public function startUpdate(): void;
}
