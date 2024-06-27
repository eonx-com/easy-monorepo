<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Common\Event;

use EonX\EasyBatch\Common\ValueObject\BatchItemInterface;

abstract class AbstractBatchItemEvent extends AbstractBatchObjectEvent
{
    public function __construct(BatchItemInterface $batchItem)
    {
        parent::__construct($batchItem);
    }

    public function getBatchItem(): BatchItemInterface
    {
        /** @var \EonX\EasyBatch\Common\ValueObject\BatchItemInterface $batchItem */
        $batchItem = $this->getBatchObject();

        return $batchItem;
    }

    public function setBatchItem(BatchItemInterface $batchItem): void
    {
        $this->setBatchObject($batchItem);
    }
}
