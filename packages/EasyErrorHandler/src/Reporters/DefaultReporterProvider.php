<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Reporters;

use EonX\EasyErrorHandler\Interfaces\ErrorDetailsResolverInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorReporterProviderInterface;
use Psr\Log\LoggerInterface;

final class DefaultReporterProvider implements ErrorReporterProviderInterface
{
    /**
     * @var \EonX\EasyErrorHandler\Interfaces\ErrorDetailsResolverInterface
     */
    private $errorDetailsResolver;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(ErrorDetailsResolverInterface $errorDetailsResolver, LoggerInterface $logger)
    {
        $this->errorDetailsResolver = $errorDetailsResolver;
        $this->logger = $logger;
    }

    /**
     * @return iterable<\EonX\EasyErrorHandler\Interfaces\ErrorReporterInterface>
     */
    public function getReporters(): iterable
    {
        yield new LoggerReporter($this->errorDetailsResolver, $this->logger);
    }
}
