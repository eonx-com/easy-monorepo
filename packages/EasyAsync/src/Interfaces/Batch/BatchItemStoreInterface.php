<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Interfaces\Batch;

interface BatchItemStoreInterface
{
    public function store(BatchItemInterface $batchItem): BatchItemInterface;
}
