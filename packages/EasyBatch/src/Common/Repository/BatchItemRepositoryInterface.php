<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Common\Repository;

use EonX\EasyBatch\Common\ValueObject\BatchCounts;
use EonX\EasyBatch\Common\ValueObject\BatchItemInterface;
use EonX\EasyPagination\Interfaces\LengthAwarePaginatorInterface;
use EonX\EasyPagination\Interfaces\PaginationInterface;

interface BatchItemRepositoryInterface
{
    public const DEFAULT_TABLE = 'easy_batch_items';

    public function findCountsForBatch(int|string $batchId): BatchCounts;

    /**
     * @throws \EonX\EasyBatch\Common\Exception\BatchItemNotFoundException
     */
    public function findForProcess(int|string $batchItemId): BatchItemInterface;

    /**
     * @throws \EonX\EasyBatch\Common\Exception\BatchItemNotFoundException
     */
    public function findOrFail(int|string $batchItemId): BatchItemInterface;

    public function paginateItems(
        PaginationInterface $pagination,
        int|string $batchId,
        ?string $dependsOnName = null,
    ): LengthAwarePaginatorInterface;

    public function save(BatchItemInterface $batchItem): BatchItemInterface;

    /**
     * @param \EonX\EasyBatch\Common\ValueObject\BatchItemInterface[] $batchItems
     */
    public function updateStatusToPending(array $batchItems): void;
}
