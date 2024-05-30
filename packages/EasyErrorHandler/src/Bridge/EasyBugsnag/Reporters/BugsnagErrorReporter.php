<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\EasyBugsnag\Reporters;

use Bugsnag\Client;
use EonX\EasyErrorHandler\Interfaces\ErrorLogLevelResolverInterface;
use EonX\EasyErrorHandler\Reporters\AbstractErrorReporter;
use Monolog\Logger;
use Throwable;

final class BugsnagErrorReporter extends AbstractErrorReporter
{
    private readonly int $threshold;

    /**
     * @param \EonX\EasyErrorHandler\Bridge\EasyBugsnag\Interfaces\BugsnagExceptionIgnorerInterface[] $exceptionIgnorers
     */
    public function __construct(
        private readonly Client $bugsnag,
        private readonly iterable $exceptionIgnorers,
        ErrorLogLevelResolverInterface $errorLogLevelResolver,
        ?int $threshold = null,
        ?int $priority = null,
    ) {
        $this->threshold = $threshold ?? Logger::ERROR;

        parent::__construct($errorLogLevelResolver, $priority);
    }

    public function report(Throwable $throwable): void
    {
        foreach ($this->exceptionIgnorers as $ignorer) {
            if ($ignorer->shouldIgnore($throwable)) {
                return;
            }
        }

        $logLevel = $this->errorLogLevelResolver->getLogLevel($throwable);

        if ($logLevel >= $this->threshold) {
            $this->bugsnag->notifyException($throwable);
        }
    }
}
