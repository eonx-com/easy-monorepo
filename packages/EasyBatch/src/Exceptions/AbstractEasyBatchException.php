<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Exceptions;

use EonX\EasyBatch\Interfaces\EasyBatchExceptionInterface;
use Exception;

abstract class AbstractEasyBatchException extends Exception implements EasyBatchExceptionInterface
{
    public function shouldRetry(): bool
    {
        return false;
    }
}
