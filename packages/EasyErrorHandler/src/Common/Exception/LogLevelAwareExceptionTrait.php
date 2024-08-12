<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\Exception;

use Monolog\Level;

trait LogLevelAwareExceptionTrait
{
    protected Level $logLevel = Level::Info;

    public function getLogLevel(): Level
    {
        return $this->logLevel;
    }

    public function setCriticalLogLevel(): self
    {
        return $this->setLogLevel(Level::Critical);
    }

    public function setDebugLogLevel(): self
    {
        return $this->setLogLevel(Level::Debug);
    }

    public function setErrorLogLevel(): self
    {
        return $this->setLogLevel(Level::Error);
    }

    public function setInfoLogLevel(): self
    {
        return $this->setLogLevel(Level::Info);
    }

    public function setLogLevel(Level $logLevel): self
    {
        $this->logLevel = $logLevel;

        return $this;
    }

    public function setWarningLogLevel(): self
    {
        return $this->setLogLevel(Level::Warning);
    }
}
