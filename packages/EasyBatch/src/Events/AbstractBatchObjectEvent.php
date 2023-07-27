<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Events;

use EonX\EasyBatch\Interfaces\BatchObjectInterface;

abstract class AbstractBatchObjectEvent
{
    public function __construct(
        private BatchObjectInterface $batchObject,
    ) {
    }

    public function getBatchObject(): BatchObjectInterface
    {
        return $this->batchObject;
    }

    public function setBatchObject(BatchObjectInterface $batchObject): void
    {
        $this->batchObject = $batchObject;
    }
}
