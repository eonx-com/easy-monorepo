<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Interfaces;

interface BatchRepositoryInterface
{
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

    public function save(BatchInterface $batch): BatchInterface;

    /**
     * @throws \EonX\EasyBatch\Exceptions\BatchIdRequiredException
     */
    public function updateAtomic(BatchInterface $batch, callable $func): BatchInterface;
}
