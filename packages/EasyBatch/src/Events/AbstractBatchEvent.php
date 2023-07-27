<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Events;

use EonX\EasyBatch\Interfaces\BatchInterface;

abstract class AbstractBatchEvent extends AbstractBatchObjectEvent
{
    public function __construct(BatchInterface $batch)
    {
        parent::__construct($batch);
    }

    public function getBatch(): BatchInterface
    {
        /** @var \EonX\EasyBatch\Interfaces\BatchInterface $batch */
        $batch = $this->getBatchObject();

        return $batch;
    }

    public function setBatch(BatchInterface $batch): void
    {
        $this->setBatchObject($batch);
    }
}
