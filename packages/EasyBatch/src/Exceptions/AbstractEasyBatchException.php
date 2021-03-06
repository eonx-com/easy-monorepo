<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Exceptions;

use EonX\EasyBatch\Interfaces\EasyBatchExceptionInterface;

abstract class AbstractEasyBatchException extends \Exception implements EasyBatchExceptionInterface
{
    // No body needed.
}
