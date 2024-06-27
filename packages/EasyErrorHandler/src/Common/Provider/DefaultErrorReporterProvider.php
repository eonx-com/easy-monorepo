<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\Provider;

use EonX\EasyErrorHandler\Common\Reporter\LoggerErrorReporter;
use EonX\EasyErrorHandler\Common\Resolver\ErrorDetailsResolverInterface;
use EonX\EasyErrorHandler\Common\Resolver\ErrorLogLevelResolverInterface;
use Psr\Log\LoggerInterface;

final class DefaultErrorReporterProvider implements ErrorReporterProviderInterface
{
    /**
     * @var class-string[]
     */
    private readonly array $ignoredExceptions;

    /**
     * @param class-string[]|null $ignoredExceptions
     */
    public function __construct(
        private readonly ErrorDetailsResolverInterface $errorDetailsResolver,
        private readonly ErrorLogLevelResolverInterface $errorLogLevelResolver,
        private readonly LoggerInterface $logger,
        ?array $ignoredExceptions = null,
    ) {
        $this->ignoredExceptions = $ignoredExceptions ?? [];
    }

    /**
     * @return iterable<\EonX\EasyErrorHandler\Common\Reporter\ErrorReporterInterface>
     */
    public function getReporters(): iterable
    {
        yield new LoggerErrorReporter(
            $this->errorDetailsResolver,
            $this->errorLogLevelResolver,
            $this->logger,
            $this->ignoredExceptions
        );
    }
}
