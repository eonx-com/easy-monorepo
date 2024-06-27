<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Common\Event;

use EonX\EasyBatch\Common\ValueObject\BatchInterface;

abstract class AbstractBatchEvent extends AbstractBatchObjectEvent
{
    public function __construct(BatchInterface $batch)
    {
        parent::__construct($batch);
    }

    public function getBatch(): BatchInterface
    {
        /** @var \EonX\EasyBatch\Common\ValueObject\BatchInterface $batch */
        $batch = $this->getBatchObject();

        return $batch;
    }

    public function setBatch(BatchInterface $batch): void
    {
        $this->setBatchObject($batch);
    }
}
