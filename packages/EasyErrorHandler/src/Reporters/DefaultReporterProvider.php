<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Reporters;

use EonX\EasyErrorHandler\Interfaces\ErrorDetailsResolverInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorLogLevelResolverInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorReporterProviderInterface;
use Psr\Log\LoggerInterface;

final class DefaultReporterProvider implements ErrorReporterProviderInterface
{
    /**
     * @var \EonX\EasyErrorHandler\Interfaces\ErrorDetailsResolverInterface
     */
    private $errorDetailsResolver;

    /**
     * @var \EonX\EasyErrorHandler\Interfaces\ErrorLogLevelResolverInterface
     */
    private $errorLogLevelResolver;

    /**
     * @var null|string[]
     */
    private $ignoredExceptions;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @param null|string[] $ignoredExceptions
     */
    public function __construct(
        ErrorDetailsResolverInterface $errorDetailsResolver,
        ErrorLogLevelResolverInterface $errorLogLevelResolver,
        LoggerInterface $logger,
        ?array $ignoredExceptions = null
    ) {
        $this->errorDetailsResolver = $errorDetailsResolver;
        $this->errorLogLevelResolver = $errorLogLevelResolver;
        $this->logger = $logger;
        $this->ignoredExceptions = $ignoredExceptions;
    }

    /**
     * @return iterable<\EonX\EasyErrorHandler\Interfaces\ErrorReporterInterface>
     */
    public function getReporters(): iterable
    {
        yield new LoggerReporter(
            $this->errorDetailsResolver,
            $this->errorLogLevelResolver,
            $this->logger,
            $this->ignoredExceptions
        );
    }
}
