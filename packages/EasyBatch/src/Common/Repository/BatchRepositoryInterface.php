<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Common\Repository;

use EonX\EasyBatch\Common\ValueObject\BatchInterface;

interface BatchRepositoryInterface
{
    public const DEFAULT_TABLE = 'easy_batches';

    public function find(int|string $id): ?BatchInterface;

    public function findNestedOrFail(int|string $parentBatchItemId): BatchInterface;

    /**
     * @throws \EonX\EasyBatch\Common\Exception\BatchNotFoundException
     */
    public function findOrFail(int|string $id): BatchInterface;

    public function reset(): self;

    public function save(BatchInterface $batch): BatchInterface;

    /**
     * @throws \EonX\EasyBatch\Common\Exception\BatchObjectIdRequiredException
     */
    public function updateAtomic(BatchInterface $batch, callable $func): BatchInterface;
}
