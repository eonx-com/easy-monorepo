<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Common\Exception;

use EonX\EasyBatch\Common\ValueObject\BatchItemInterface;
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
