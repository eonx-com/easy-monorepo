<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Interfaces;

interface BatchRepositoryInterface
{
    /**
     * @var string
     */
    public const DEFAULT_TABLE = 'easy_batches';

    /**
     * @param int|string $id
     */
    public function find($id): ?BatchInterface;

    /**
     * @param int|string $id
     *
     * @throws \EonX\EasyBatch\Exceptions\BatchNotFoundException
     */
    public function findOrFail($id): BatchInterface;

    /**
     * @param int|string $parentBatchItemId
     */
    public function findNestedOrFail($parentBatchItemId): BatchInterface;

    public function save(BatchInterface $batch): BatchInterface;

    /**
     * @throws \EonX\EasyBatch\Exceptions\BatchObjectIdRequiredException
     */
    public function updateAtomic(BatchInterface $batch, callable $func): BatchInterface;
}
