<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Exceptions\Traits;

use EonX\EasyLogging\Interfaces\LoggerInterface;

trait LogLevelAwareExceptionTrait
{
    /**
     * @var string
     */
    protected $logLevel = LoggerInterface::LEVEL_ERROR;

    /**
     * {@inheritdoc}
     */
    public function getLogLevel(): string
    {
        return $this->logLevel;
    }

    /**
     * Sets the log level for an exception.
     */
    public function setLogLevel(string $logLevel): self
    {
        $this->logLevel = $logLevel;

        return $this;
    }
}
