<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Interfaces;

/**
 * @deprecated since 4.1, will be removed in 5.0. Use EonX\EasyBatch\Interfaces\CurrentBatchObjectsAwareInterface instead.
 */
interface CurrentBatchAwareInterface
{
    public function setCurrentBatch(BatchInterface $batch): void;
}
