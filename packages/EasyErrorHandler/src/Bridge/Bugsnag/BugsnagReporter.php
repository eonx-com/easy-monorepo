<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Bugsnag;

use Bugsnag\Client;
use EonX\EasyErrorHandler\Reporters\AbstractErrorReporter;
use Monolog\Logger;
use Throwable;

final class BugsnagReporter extends AbstractErrorReporter
{
    /**
     * @var \Bugsnag\Client
     */
    private $bugsnag;

    /**
     * @var int
     */
    private $threshold;

    public function __construct(Client $bugsnag, ?int $threshold = null, ?int $priority = null)
    {
        $this->bugsnag = $bugsnag;
        $this->threshold = $threshold ?? Logger::ERROR;

        parent::__construct($priority);
    }

    /**
     * @return void|bool
     */
    public function report(Throwable $throwable)
    {
        $logLevel = $this->getLogLevel($throwable);

        if ($logLevel >= $this->threshold) {
            $this->bugsnag->notifyException($throwable);
        }
    }
}
