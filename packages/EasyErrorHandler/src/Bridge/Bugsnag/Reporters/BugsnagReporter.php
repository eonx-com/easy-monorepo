<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Bugsnag\Reporters;

use Bugsnag\Client;
use EonX\EasyErrorHandler\Bridge\Bugsnag\Interfaces\BugsnagIgnoreExceptionsResolverInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorLogLevelResolverInterface;
use EonX\EasyErrorHandler\Reporters\AbstractErrorReporter;
use Monolog\Logger;
use Throwable;

final class BugsnagReporter extends AbstractErrorReporter
{
    private Client $bugsnag;

    private BugsnagIgnoreExceptionsResolverInterface $ignoreExceptionsResolver;

    private ?int $threshold;

    public function __construct(
        Client $bugsnag,
        BugsnagIgnoreExceptionsResolverInterface $ignoreExceptionsResolver,
        ErrorLogLevelResolverInterface $errorLogLevelResolver,
        ?int $threshold = null,
        ?int $priority = null
    ) {
        $this->bugsnag = $bugsnag;
        $this->threshold = $threshold ?? Logger::ERROR;

        $this->ignoreExceptionsResolver = $ignoreExceptionsResolver;

        parent::__construct($errorLogLevelResolver, $priority);
    }

    /**
     * @return void|bool
     */
    public function report(Throwable $throwable)
    {
        if ($this->ignoreExceptionsResolver->shouldIgnore($throwable) === true) {
            return;
        }

        $logLevel = $this->errorLogLevelResolver->getLogLevel($throwable);

        if ($logLevel >= $this->threshold) {
            $this->bugsnag->notifyException($throwable);
        }
    }
}
