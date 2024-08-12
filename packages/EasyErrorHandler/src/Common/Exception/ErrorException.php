<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\Exception;

use Monolog\Level;

abstract class ErrorException extends BaseException
{
    protected Level $logLevel = Level::Error;
}
