<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Events;

use EonX\EasyBatch\Interfaces\BatchItemInterface;

final class BatchItemNotProcessedEvent extends AbstractBatchItemEvent
{
    /**
     * @var null|\Throwable
     */
    private $throwable;

    public function __construct(BatchItemInterface $batchItem, ?\Throwable $throwable = null)
    {
        $this->throwable = $throwable;

        parent::__construct($batchItem);
    }

    public function getThrowable(): ?\Throwable
    {
        return $this->throwable;
    }
}
