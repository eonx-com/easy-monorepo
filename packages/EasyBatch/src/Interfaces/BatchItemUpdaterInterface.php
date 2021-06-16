<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Interfaces;

interface BatchItemUpdaterInterface
{
    public function update(BatchItemInterface $batchItem): BatchItemInterface;

    public function updateNotProcessed(BatchItemInterface $batchItem, ?\Throwable $throwable = null): BatchItemInterface;
}
