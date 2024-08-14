<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Common\ValueObject;

trait CurrentBatchObjectsAwareTrait
{
    protected ?Batch $currentBatch = null;

    protected ?BatchItem $currentBatchItem = null;

    public function setCurrentBatchObjects(Batch $batch, BatchItem $batchItem): void
    {
        $this->currentBatch = $batch;
        $this->currentBatchItem = $batchItem;
    }

    public function unsetCurrentBatchObjects(): void
    {
        $this->currentBatch = null;
        $this->currentBatchItem = null;
    }
}
