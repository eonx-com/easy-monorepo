<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Exceptions;

use EonX\EasyBatch\Interfaces\BatchItemInterface;

final class BatchItemSavedButBatchNotProcessedException extends AbstractEasyBatchEmergencyException
{
    public function __construct(private readonly BatchItemInterface $batchItem)
    {
        parent::__construct();
    }

    public function getBatchItem(): BatchItemInterface
    {
        return $this->batchItem;
    }
}
