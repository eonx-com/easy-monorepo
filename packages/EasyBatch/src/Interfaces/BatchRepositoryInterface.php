<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Interfaces;

interface BatchRepositoryInterface
{
    /**
     * @var string
     */
    public const DEFAULT_TABLE = 'easy_batches';

    public function find(int|string $id): ?BatchInterface;

    /**
     * @throws \EonX\EasyBatch\Exceptions\BatchNotFoundException
     */
    public function findOrFail(int|string $id): BatchInterface;

    public function findNestedOrFail(int|string $parentBatchItemId): BatchInterface;

    public function reset(): self;

    public function save(BatchInterface $batch): BatchInterface;

    /**
     * @throws \EonX\EasyBatch\Exceptions\BatchObjectIdRequiredException
     */
    public function updateAtomic(BatchInterface $batch, callable $func): BatchInterface;
}
