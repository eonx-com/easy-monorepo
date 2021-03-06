<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Interfaces;

use EonX\EasyPagination\Interfaces\LengthAwarePaginatorInterface;
use EonX\EasyPagination\Interfaces\StartSizeDataInterface;

interface BatchItemRepositoryInterface
{
    /**
     * @var string
     */
    public const DEFAULT_TABLE = 'easy_batch_items';

    /**
     * @param int|string $batchId
     */
    public function findForDispatch(
        StartSizeDataInterface $startSizeData,
        $batchId,
        ?string $dependsOnName = null
    ): LengthAwarePaginatorInterface;

    /**
     * @param int|string $batchItemId
     *
     * @throws \EonX\EasyBatch\Exceptions\BatchItemNotFoundException
     */
    public function findOrFail($batchItemId): BatchItemInterface;

    public function save(BatchItemInterface $batchItem): BatchItemInterface;
}
