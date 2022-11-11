<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Exceptions;

final class BatchItemNotHandledException extends AbstractEasyBatchException
{
    public function shouldRetry(): bool
    {
        return true;
    }
}
