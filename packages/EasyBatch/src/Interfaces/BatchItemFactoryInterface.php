<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Interfaces;

interface BatchItemFactoryInterface extends BatchObjectFactoryInterface
{
    public function create(int|string $batchId, ?object $message = null, ?string $class = null): BatchItemInterface;
}
