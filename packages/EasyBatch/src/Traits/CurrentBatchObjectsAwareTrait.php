<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Traits;

use EonX\EasyBatch\Interfaces\BatchInterface;
use EonX\EasyBatch\Interfaces\BatchItemInterface;

trait CurrentBatchObjectsAwareTrait
{
    protected ?BatchInterface $currentBatch = null;

    protected ?BatchItemInterface $currentBatchItem = null;

    public function setCurrentBatchObjects(BatchInterface $batch, BatchItemInterface $batchItem): void
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
