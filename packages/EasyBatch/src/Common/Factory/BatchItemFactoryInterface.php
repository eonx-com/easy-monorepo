<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Common\Factory;

use EonX\EasyBatch\Common\ValueObject\BatchItemInterface;

interface BatchItemFactoryInterface extends BatchObjectFactoryInterface
{
    public function create(int|string $batchId, ?object $message = null, ?string $class = null): BatchItemInterface;
}
