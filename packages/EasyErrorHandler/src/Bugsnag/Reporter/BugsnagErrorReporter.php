<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bugsnag\Reporter;

use Bugsnag\Client;
use EonX\EasyErrorHandler\Common\Reporter\AbstractErrorReporter;
use EonX\EasyErrorHandler\Common\Resolver\ErrorLogLevelResolverInterface;
use Monolog\Level;
use Throwable;

final class BugsnagErrorReporter extends AbstractErrorReporter
{
    private readonly Level $threshold;

    /**
     * @param \EonX\EasyErrorHandler\Bugsnag\Ignorer\BugsnagExceptionIgnorerInterface[] $exceptionIgnorers
     */
    public function __construct(
        private readonly Client $bugsnag,
        private readonly iterable $exceptionIgnorers,
        ErrorLogLevelResolverInterface $errorLogLevelResolver,
        ?Level $threshold = null,
        ?int $priority = null,
    ) {
        $this->threshold = $threshold ?? Level::Error;

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

        if ($logLevel->value >= $this->threshold->value) {
            $this->bugsnag->notifyException($throwable);
        }
    }
}
