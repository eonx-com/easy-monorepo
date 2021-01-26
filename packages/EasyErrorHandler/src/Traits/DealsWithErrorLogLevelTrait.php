<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Traits;

use EonX\EasyErrorHandler\Interfaces\Exceptions\LogLevelAwareExceptionInterface;
use Monolog\Logger;
use Throwable;

trait DealsWithErrorLogLevelTrait
{
    protected function getLogLevel(Throwable $throwable): int
    {
        if ($throwable instanceof LogLevelAwareExceptionInterface) {
            return $throwable->getLogLevel();
        }

        // Default to error as exceptions not aware of their log level is unexpected
        return Logger::ERROR;
    }
}
