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

    public function setLogLevel(int $logLevel): self
    {
        $this->logLevel = $logLevel;

        return $this;
    }
}
