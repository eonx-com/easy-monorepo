<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Common\Manager;

use EonX\EasyBatch\Common\ValueObject\BatchInterface;
use EonX\EasyBatch\Common\ValueObject\BatchObjectInterface;

interface BatchObjectManagerInterface
{
    public function approve(BatchObjectInterface $batchObject): BatchObjectInterface;

    public function cancel(BatchObjectInterface $batchObject): BatchObjectInterface;

    public function dispatchBatch(BatchInterface $batch, ?callable $beforeFirstDispatch = null): BatchInterface;

    public function restoreBatchState(int|string $batchId): BatchInterface;
}