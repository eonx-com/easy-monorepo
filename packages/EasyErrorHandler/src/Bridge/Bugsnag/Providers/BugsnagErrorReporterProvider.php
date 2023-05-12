<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Bugsnag\Providers;

use Bugsnag\Client;
use EonX\EasyErrorHandler\Bridge\Bugsnag\Interfaces\BugsnagIgnoreExceptionsResolverInterface;
use EonX\EasyErrorHandler\Bridge\Bugsnag\Reporters\BugsnagErrorReporter;
use EonX\EasyErrorHandler\Interfaces\ErrorLogLevelResolverInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorReporterProviderInterface;

final class BugsnagErrorReporterProvider implements ErrorReporterProviderInterface
{
    public function __construct(
        private readonly Client $bugsnag,
        private readonly BugsnagIgnoreExceptionsResolverInterface $bugsnagIgnoreExceptionsResolver,
        private readonly ErrorLogLevelResolverInterface $errorLogLevelResolver,
        private readonly ?int $threshold = null
    ) {
    }

    /**
     * @return iterable<\EonX\EasyErrorHandler\Interfaces\ErrorReporterInterface>
     */
    public function getReporters(): iterable
    {
        yield new BugsnagErrorReporter(
            $this->bugsnag,
            $this->bugsnagIgnoreExceptionsResolver,
            $this->errorLogLevelResolver,
            $this->threshold
        );
    }
}
