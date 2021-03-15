<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Interfaces\Batch;

interface BatchUpdaterInterface
{
    public function updateForItem(BatchInterface $batch, BatchItemInterface $batchItem): BatchInterface;
}
