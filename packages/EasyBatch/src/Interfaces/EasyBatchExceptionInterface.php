<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Interfaces;

interface EasyBatchExceptionInterface
{
    public function shouldRetry(): bool;
}
