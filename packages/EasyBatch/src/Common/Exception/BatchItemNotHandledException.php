<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Common\Exception;

final class BatchItemNotHandledException extends AbstractEasyBatchException
{
    public function shouldRetry(): bool
    {
        return true;
    }
}
