<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Reporters;

use EonX\EasyErrorHandler\Interfaces\ErrorDetailsResolverInterface;
use Psr\Log\LoggerInterface;
use Throwable;

final class LoggerReporter extends AbstractErrorReporter
{
    /**
     * @var \EonX\EasyErrorHandler\Interfaces\ErrorDetailsResolverInterface
     */
    private $errorDetailsResolver;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(
        ErrorDetailsResolverInterface $errorDetailsResolver,
        LoggerInterface $logger,
        ?int $priority = null
    ) {
        $this->errorDetailsResolver = $errorDetailsResolver;
        $this->logger = $logger;

        parent::__construct($priority);
    }

    /**
     * @return void|bool
     */
    public function report(Throwable $throwable)
    {
        $this->logger->log(
            $this->getLogLevel($throwable),
            $throwable->getMessage(),
            [
                'exception' => $this->errorDetailsResolver->resolveExtendedDetails($throwable),
            ]
        );
    }
}
