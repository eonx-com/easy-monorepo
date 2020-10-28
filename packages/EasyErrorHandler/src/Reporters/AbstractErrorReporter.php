<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Reporters;

use EonX\EasyErrorHandler\Interfaces\ErrorReporterInterface;
use EonX\EasyErrorHandler\Interfaces\Exceptions\LogLevelAwareExceptionInterface;
use Monolog\Logger;
use Throwable;

abstract class AbstractErrorReporter implements ErrorReporterInterface
{
    /**
     * @var int
     */
    private $priority;

    public function __construct(?int $priority = null)
    {
        $this->priority = $priority ?? 0;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    protected function getLogLevel(Throwable $throwable): int
    {
        if ($throwable instanceof LogLevelAwareExceptionInterface) {
            return $throwable->getLogLevel();
        }

        // Default to error as exceptions not aware of their log level is unexpected
        return Logger::ERROR;
    }
}
