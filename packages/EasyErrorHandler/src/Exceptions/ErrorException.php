<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Exceptions;

use Monolog\Logger;

abstract class ErrorException extends BaseException
{
    protected int $logLevel = Logger::ERROR;
}
