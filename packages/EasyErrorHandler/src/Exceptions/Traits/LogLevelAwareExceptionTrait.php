<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Exceptions\Traits;

use Monolog\Logger;

trait LogLevelAwareExceptionTrait
{
    /**
     * @var int
     */
    protected $logLevel = Logger::INFO;

    public function getLogLevel(): int
    {
        return $this->logLevel;
    }

    public function logAsCritical(): self
    {
        return $this->setLogLevel(Logger::CRITICAL);
    }

    public function logAsDebug(): self
    {
        return $this->setLogLevel(Logger::DEBUG);
    }

    public function logAsError(): self
    {
        return $this->setLogLevel(Logger::ERROR);
    }

    public function logAsInfo(): self
    {
        return $this->setLogLevel(Logger::INFO);
    }

    public function logAsWarning(): self
    {
        return $this->setLogLevel(Logger::WARNING);
    }

    public function setLogLevel(int $logLevel): self
    {
        $this->logLevel = $logLevel;

        return $this;
    }
}
