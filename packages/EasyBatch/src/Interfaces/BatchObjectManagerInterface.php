<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Interfaces;

interface BatchObjectManagerInterface
{
    public const DEFAULT_BATCH_ITEMS_PER_PAGE = 15;

    public function approve(BatchObjectInterface $batchObject): BatchObjectInterface;

    public function cancel(BatchObjectInterface $batchObject): BatchObjectInterface;

    public function dispatchBatch(BatchInterface $batch, ?callable $beforeFirstDispatch = null): BatchInterface;

    public function restoreBatchState(int|string $batchId): BatchInterface;
}
