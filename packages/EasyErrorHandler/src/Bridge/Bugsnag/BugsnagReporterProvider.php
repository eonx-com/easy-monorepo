<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Bugsnag;

use Bugsnag\Client;
use EonX\EasyErrorHandler\Interfaces\BugsnagIgnoreExceptionsResolverInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorLogLevelResolverInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorReporterProviderInterface;

final class BugsnagReporterProvider implements ErrorReporterProviderInterface
{
    /**
     * @param string[]|null $ignoredExceptions
     */
    public function __construct(
        private Client $bugsnag,
        private BugsnagIgnoreExceptionsResolverInterface $bugsnagIgnoreExceptionsResolver,
        private ErrorLogLevelResolverInterface $errorLogLevelResolver,
        private ?int $threshold = null,
        private ?array $ignoredExceptions = null
    ) {
    }

    /**
     * @return iterable<\EonX\EasyErrorHandler\Interfaces\ErrorReporterInterface>
     */
    public function getReporters(): iterable
    {
        yield new BugsnagReporter(
            $this->bugsnag,
            $this->bugsnagIgnoreExceptionsResolver,
            $this->errorLogLevelResolver,
            $this->threshold,
            $this->ignoredExceptions
        );
    }
}
