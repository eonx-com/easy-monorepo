<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Interfaces;

use EonX\EasyBatch\Repositories\BatchCountsDto;
use EonX\EasyPagination\Interfaces\LengthAwarePaginatorInterface;
use EonX\EasyPagination\Interfaces\PaginationInterface;

interface BatchItemRepositoryInterface
{
    /**
     * @var string
     */
    public const DEFAULT_TABLE = 'easy_batch_items';

    public function findCountsForBatch(int|string $batchId): BatchCountsDto;

    /**
     * @throws \EonX\EasyBatch\Exceptions\BatchItemNotFoundException
     */
    public function findForProcess(int|string $batchItemId): BatchItemInterface;

    /**
     * @throws \EonX\EasyBatch\Exceptions\BatchItemNotFoundException
     */
    public function findOrFail(int|string $batchItemId): BatchItemInterface;

    public function paginateItems(
        PaginationInterface $pagination,
        int|string $batchId,
        ?string $dependsOnName = null,
    ): LengthAwarePaginatorInterface;

    public function save(BatchItemInterface $batchItem): BatchItemInterface;

    /**
     * @param \EonX\EasyBatch\Interfaces\BatchItemInterface[] $batchItems
     */
    public function updateStatusToPending(array $batchItems): void;
}
