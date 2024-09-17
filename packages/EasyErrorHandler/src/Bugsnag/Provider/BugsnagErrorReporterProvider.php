<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bugsnag\Provider;

use Bugsnag\Client;
use EonX\EasyErrorHandler\Bugsnag\Reporter\BugsnagErrorReporter;
use EonX\EasyErrorHandler\Common\Provider\ErrorReporterProviderInterface;
use EonX\EasyErrorHandler\Common\Resolver\ErrorLogLevelResolverInterface;
use Monolog\Level;

final readonly class BugsnagErrorReporterProvider implements ErrorReporterProviderInterface
{
    /**
     * @param \EonX\EasyErrorHandler\Bugsnag\Ignorer\BugsnagExceptionIgnorerInterface[] $exceptionIgnorers
     */
    public function __construct(
        private Client $bugsnag,
        private readonly iterable $exceptionIgnorers,
        private ErrorLogLevelResolverInterface $errorLogLevelResolver,
        private ?Level $threshold = null,
    ) {
    }

    /**
     * @return iterable<\EonX\EasyErrorHandler\Common\Reporter\ErrorReporterInterface>
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
