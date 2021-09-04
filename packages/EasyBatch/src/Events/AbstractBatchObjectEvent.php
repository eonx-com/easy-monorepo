<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Events;

use EonX\EasyBatch\Interfaces\BatchObjectInterface;

abstract class AbstractBatchObjectEvent
{
    /**
     * @var \EonX\EasyBatch\Interfaces\BatchObjectInterface
     */
    private $batchObject;

    public function __construct(BatchObjectInterface $batchObject)
    {
        $this->batchObject = $batchObject;
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
