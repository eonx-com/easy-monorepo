<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Providers;

use EonX\EasyErrorHandler\Interfaces\ErrorDetailsResolverInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorLogLevelResolverInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorReporterProviderInterface;
use EonX\EasyErrorHandler\Reporters\LoggerErrorReporter;
use Psr\Log\LoggerInterface;

final class DefaultErrorReporterProvider implements ErrorReporterProviderInterface
{
    /**
     * @var class-string[]
     */
    private readonly array $ignoredExceptions;

    /**
     * @param null|class-string[] $ignoredExceptions
     */
    public function __construct(
        private readonly ErrorDetailsResolverInterface $errorDetailsResolver,
        private readonly ErrorLogLevelResolverInterface $errorLogLevelResolver,
        private readonly LoggerInterface $logger,
        ?array $ignoredExceptions = null
    ) {
        $this->ignoredExceptions = $ignoredExceptions ?? [];
    }

    /**
     * @return iterable<\EonX\EasyErrorHandler\Interfaces\ErrorReporterInterface>
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
