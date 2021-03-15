<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Interfaces\Batch;

interface BatchItemFactoryInterface
{
    public function create(string $batchId, string $targetClass, ?string $id = null): BatchItemInterface;
}
