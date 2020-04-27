<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Interfaces;

interface LogLevelAwareExceptionInterface
{
    /**
     * Returns the log level of an exception.
     */
    public function getLogLevel(): string;

    /**
     * Sets the log level for an exception.
     */
    public function setLogLevel(string $logLevel);
}
