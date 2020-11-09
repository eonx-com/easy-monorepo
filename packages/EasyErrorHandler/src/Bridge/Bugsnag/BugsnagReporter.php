<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Bugsnag;

use Bugsnag\Client;
use EonX\EasyErrorHandler\Reporters\AbstractErrorReporter;
use Monolog\Logger;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

final class BugsnagReporter extends AbstractErrorReporter
{
    /**
     * @var \Bugsnag\Client
     */
    private $bugsnag;

    /**
     * @var string[]
     */
    private $ignoreExceptions;

    /**
     * @var int
     */
    private $threshold;

    public function __construct(
        Client $bugsnag,
        ?int $threshold = null,
        ?array $ignoreExceptions = null,
        ?int $priority = null
    ) {
        $this->bugsnag = $bugsnag;
        $this->threshold = $threshold ?? Logger::ERROR;

        // TODO - Implement configuration to allow apps to customize the list
        $this->ignoreExceptions = $ignoreExceptions ?? [
                HttpExceptionInterface::class,
            ];

        parent::__construct($priority);
    }

    /**
     * @return void|bool
     */
    public function report(Throwable $throwable)
    {
        $exceptionClass = \get_class($throwable);
        foreach ($this->ignoreExceptions as $ignoreClass) {
            if (\is_a($exceptionClass, $ignoreClass, true)) {
                return;
            }
        }

        $logLevel = $this->getLogLevel($throwable);

        if ($logLevel >= $this->threshold) {
            $this->bugsnag->notifyException($throwable);
        }
    }
}
