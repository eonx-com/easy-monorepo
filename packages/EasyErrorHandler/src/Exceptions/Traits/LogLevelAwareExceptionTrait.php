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

    public function setCriticalLogLevel(): self
    {
        return $this->setLogLevel(Logger::CRITICAL);
    }

    public function setDebugLogLevel(): self
    {
        return $this->setLogLevel(Logger::DEBUG);
    }

    public function setErrorLogLevel(): self
    {
        return $this->setLogLevel(Logger::ERROR);
    }

    public function setInfoLogLevel(): self
    {
        return $this->setLogLevel(Logger::INFO);
    }

    public function setLogLevel(int $logLevel): self
    {
        $this->logLevel = $logLevel;

        return $this;
    }

    public function setWarningLogLevel(): self
    {
        return $this->setLogLevel(Logger::WARNING);
    }
}
