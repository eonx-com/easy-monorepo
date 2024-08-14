<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Common\Event;

use EonX\EasyBatch\Common\ValueObject\Batch;

abstract class AbstractBatchEvent extends AbstractBatchObjectEvent
{
    public function __construct(Batch $batch)
    {
        parent::__construct($batch);
    }

    public function getBatch(): Batch
    {
        /** @var \EonX\EasyBatch\Common\ValueObject\Batch $batch */
        $batch = $this->getBatchObject();

        return $batch;
    }

    public function setBatch(Batch $batch): void
    {
        $this->setBatchObject($batch);
    }
}
