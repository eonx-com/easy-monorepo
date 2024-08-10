<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Common\Repository;

use EonX\EasyBatch\Common\ValueObject\BatchCounts;
use EonX\EasyBatch\Common\ValueObject\BatchItem;
use EonX\EasyPagination\Paginator\LengthAwarePaginatorInterface;
use EonX\EasyPagination\ValueObject\Pagination;

interface BatchItemRepositoryInterface
{
    public function findCountsForBatch(int|string $batchId): BatchCounts;

    /**
     * @throws \EonX\EasyBatch\Common\Exception\BatchItemNotFoundException
     */
    public function findForProcess(int|string $batchItemId): BatchItem;

    /**
     * @throws \EonX\EasyBatch\Common\Exception\BatchItemNotFoundException
     */
    public function findOrFail(int|string $batchItemId): BatchItem;

    public function paginateItems(
        Pagination $pagination,
        int|string $batchId,
        ?string $dependsOnName = null,
    ): LengthAwarePaginatorInterface;

    public function save(BatchItem $batchItem): BatchItem;

    /**
     * @param \EonX\EasyBatch\Common\ValueObject\BatchItem[] $batchItems
     */
    public function updateStatusToPending(array $batchItems): void;
}
