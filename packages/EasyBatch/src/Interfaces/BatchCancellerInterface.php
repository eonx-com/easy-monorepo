<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Interfaces;

interface BatchCancellerInterface
{
    public function cancel(BatchInterface $batch, ?\Throwable $throwable = null): BatchInterface;
}
