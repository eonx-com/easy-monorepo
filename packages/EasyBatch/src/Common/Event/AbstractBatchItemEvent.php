<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Common\Event;

use EonX\EasyBatch\Common\ValueObject\BatchItem;

abstract class AbstractBatchItemEvent extends AbstractBatchObjectEvent
{
    public function __construct(BatchItem $batchItem)
    {
        parent::__construct($batchItem);
    }

    public function getBatchItem(): BatchItem
    {
        /** @var \EonX\EasyBatch\Common\ValueObject\BatchItem $batchItem */
        $batchItem = $this->getBatchObject();

        return $batchItem;
    }

    public function setBatchItem(BatchItem $batchItem): void
    {
        $this->setBatchObject($batchItem);
    }
}
