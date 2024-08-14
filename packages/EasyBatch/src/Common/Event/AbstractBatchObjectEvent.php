<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Common\Event;

use EonX\EasyBatch\Common\ValueObject\AbstractBatchObject;

abstract class AbstractBatchObjectEvent
{
    public function __construct(
        private AbstractBatchObject $batchObject,
    ) {
    }

    public function getBatchObject(): AbstractBatchObject
    {
        return $this->batchObject;
    }

    public function setBatchObject(AbstractBatchObject $batchObject): void
    {
        $this->batchObject = $batchObject;
    }
}
