<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Common\Manager;

use EonX\EasyBatch\Common\ValueObject\AbstractBatchObject;
use EonX\EasyBatch\Common\ValueObject\Batch;

interface BatchObjectManagerInterface
{
    public function approve(AbstractBatchObject $batchObject): AbstractBatchObject;

    public function cancel(AbstractBatchObject $batchObject): AbstractBatchObject;

    public function dispatchBatch(Batch $batch, ?callable $beforeFirstDispatch = null): Batch;

    public function restoreBatchState(int|string $batchId): Batch;
}
