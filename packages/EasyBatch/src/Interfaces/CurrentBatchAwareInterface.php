<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Interfaces;

interface CurrentBatchAwareInterface
{
    public function setCurrentBatch(BatchInterface $batch): void;
}
