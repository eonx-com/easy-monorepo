<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Interfaces;

interface BatchItemFactoryInterface extends BatchObjectFactoryInterface
{
    /**
     * @param int|string $batchId
     */
    public function create($batchId, string $targetClass, ?string $class = null): BatchItemInterface;
}
