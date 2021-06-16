<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Interfaces;

interface BatchUpdaterInterface
{
    public function updateForItem(BatchInterface $batch, BatchItemInterface $batchItem): BatchInterface;

    public function updateTotal(BatchInterface $batch, int $add): BatchInterface;
}
