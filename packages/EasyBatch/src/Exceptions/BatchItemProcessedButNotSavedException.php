<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Exceptions;

use EonX\EasyBatch\Interfaces\BatchItemInterface;
use Throwable;

final class BatchItemProcessedButNotSavedException extends AbstractEasyBatchEmergencyException
{
    public function __construct(
        private readonly BatchItemInterface $batchItem,
        Throwable $previous,
    ) {
        parent::__construct(previous: $previous);
    }

    public function getBatchItem(): BatchItemInterface
    {
        return $this->batchItem;
    }
}
