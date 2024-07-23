<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bugsnag\Provider;

use Bugsnag\Client;
use EonX\EasyErrorHandler\Bugsnag\Reporter\BugsnagErrorReporter;
use EonX\EasyErrorHandler\Bugsnag\Resolver\BugsnagIgnoreExceptionsResolverInterface;
use EonX\EasyErrorHandler\Common\Provider\ErrorReporterProviderInterface;
use EonX\EasyErrorHandler\Common\Resolver\ErrorLogLevelResolverInterface;

final readonly class BugsnagErrorReporterProvider implements ErrorReporterProviderInterface
{
    public function __construct(
        private Client $bugsnag,
        private BugsnagIgnoreExceptionsResolverInterface $bugsnagIgnoreExceptionsResolver,
        private ErrorLogLevelResolverInterface $errorLogLevelResolver,
        private ?int $threshold = null,
    ) {
    }

    /**
     * @return iterable<\EonX\EasyErrorHandler\Common\Reporter\ErrorReporterInterface>
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
