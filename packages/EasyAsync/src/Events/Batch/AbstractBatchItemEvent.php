<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Events\Batch;

use EonX\EasyAsync\Interfaces\Batch\BatchItemInterface;
use EonX\EasyAsync\Interfaces\EasyAsyncEventInterface;

abstract class AbstractBatchItemEvent implements EasyAsyncEventInterface
{
    /**
     * @var \EonX\EasyAsync\Interfaces\Batch\BatchItemInterface
     */
    private $batchItem;

    public function __construct(BatchItemInterface $batchItem)
    {
        $this->batchItem = $batchItem;
    }

    public function getBatchItem(): BatchItemInterface
    {
        return $this->batchItem;
    }
}
