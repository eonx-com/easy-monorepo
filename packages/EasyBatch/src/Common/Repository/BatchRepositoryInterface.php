<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Common\Repository;

use EonX\EasyBatch\Common\ValueObject\Batch;

interface BatchRepositoryInterface
{
    public function find(int|string $id): ?Batch;

    public function findNestedOrFail(int|string $parentBatchItemId): Batch;

    /**
     * @throws \EonX\EasyBatch\Common\Exception\BatchNotFoundException
     */
    public function findOrFail(int|string $id): Batch;

    public function reset(): self;

    public function save(Batch $batch): Batch;

    /**
     * @throws \EonX\EasyBatch\Common\Exception\BatchObjectIdRequiredException
     */
    public function updateAtomic(Batch $batch, callable $func): Batch;
}
