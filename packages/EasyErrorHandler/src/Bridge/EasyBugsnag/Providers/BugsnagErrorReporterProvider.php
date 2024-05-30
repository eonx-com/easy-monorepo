<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\EasyBugsnag\Providers;

use Bugsnag\Client;
use EonX\EasyErrorHandler\Bridge\EasyBugsnag\Reporters\BugsnagErrorReporter;
use EonX\EasyErrorHandler\Interfaces\ErrorLogLevelResolverInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorReporterProviderInterface;

final class BugsnagErrorReporterProvider implements ErrorReporterProviderInterface
{
    /**
     * @param \EonX\EasyErrorHandler\Bridge\EasyBugsnag\Interfaces\BugsnagExceptionIgnorerInterface[] $exceptionIgnorers
     */
    public function __construct(
        private readonly Client $bugsnag,
        private readonly iterable $exceptionIgnorers,
        private readonly ErrorLogLevelResolverInterface $errorLogLevelResolver,
        private readonly ?int $threshold = null,
    ) {
    }

    /**
     * @return iterable<\EonX\EasyErrorHandler\Interfaces\ErrorReporterInterface>
     */
    public function getReporters(): iterable
    {
        yield new BugsnagErrorReporter(
            $this->bugsnag,
            $this->exceptionIgnorers,
            $this->errorLogLevelResolver,
            $this->threshold
        );
    }
}
