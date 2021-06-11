<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Interfaces;

interface BatchItemRepositoryInterface
{
    public function save(BatchItemInterface $batchItem): BatchItemInterface;

    /**
     * @param int|string $id
     */
    public function find($id): ?BatchItemInterface;
}
