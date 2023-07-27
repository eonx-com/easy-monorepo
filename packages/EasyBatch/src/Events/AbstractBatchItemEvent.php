<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Events;

use EonX\EasyBatch\Interfaces\BatchItemInterface;

abstract class AbstractBatchItemEvent extends AbstractBatchObjectEvent
{
    public function __construct(BatchItemInterface $batchItem)
    {
        parent::__construct($batchItem);
    }

    public function getBatchItem(): BatchItemInterface
    {
        /** @var \EonX\EasyBatch\Interfaces\BatchItemInterface $batchItem */
        $batchItem = $this->getBatchObject();

        return $batchItem;
    }

    public function setBatchItem(BatchItemInterface $batchItem): void
    {
        $this->setBatchObject($batchItem);
    }
}
