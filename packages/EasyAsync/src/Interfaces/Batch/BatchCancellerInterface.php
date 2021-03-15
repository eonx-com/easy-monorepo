<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Interfaces\Batch;

interface BatchCancellerInterface
{
    public function cancel(BatchInterface $batch, ?\Throwable $throwable = null): BatchInterface;
}
