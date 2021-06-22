<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Interfaces;

interface CurrentBatchItemAwareInterface
{
    public function setCurrentBatchItem(BatchItemInterface $batchItem): void;
}
