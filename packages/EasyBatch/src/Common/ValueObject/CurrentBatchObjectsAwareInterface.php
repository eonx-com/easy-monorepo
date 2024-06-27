<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Common\ValueObject;

interface CurrentBatchObjectsAwareInterface
{
    public function setCurrentBatchObjects(BatchInterface $batch, BatchItemInterface $batchItem): void;

    public function unsetCurrentBatchObjects(): void;
}
