<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bugsnag\Reporter;

use Bugsnag\Client;
use EonX\EasyErrorHandler\Bugsnag\Resolver\BugsnagIgnoreExceptionsResolverInterface;
use EonX\EasyErrorHandler\Common\Reporter\AbstractErrorReporter;
use EonX\EasyErrorHandler\Common\Resolver\ErrorLogLevelResolverInterface;
use Monolog\Logger;
use Throwable;

final class BugsnagErrorReporter extends AbstractErrorReporter
{
    private readonly int $threshold;

    public function __construct(
        private readonly Client $bugsnag,
        private readonly BugsnagIgnoreExceptionsResolverInterface $ignoreExceptionsResolver,
        ErrorLogLevelResolverInterface $errorLogLevelResolver,
        ?int $threshold = null,
        ?int $priority = null,
    ) {
        $this->threshold = $threshold ?? Logger::ERROR;

        parent::__construct($errorLogLevelResolver, $priority);
    }

    public function report(Throwable $throwable): void
    {
        if ($this->ignoreExceptionsResolver->shouldIgnore($throwable)) {
            return;
        }

        $logLevel = $this->errorLogLevelResolver->getLogLevel($throwable);

        if ($logLevel >= $this->threshold) {
            $this->bugsnag->notifyException($throwable);
        }
    }
}
