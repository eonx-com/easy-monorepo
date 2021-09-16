<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Reporters;

use EonX\EasyErrorHandler\Interfaces\ErrorDetailsResolverInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorLogLevelResolverInterface;
use Psr\Log\LoggerInterface;
use Throwable;

final class LoggerReporter extends AbstractErrorReporter
{
    /**
     * @var \EonX\EasyErrorHandler\Interfaces\ErrorDetailsResolverInterface
     */
    private $errorDetailsResolver;

    /**
     * @var string[]
     */
    private $ignoreExceptions;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @param null|string[] $ignoreExceptions
     */
    public function __construct(
        ErrorDetailsResolverInterface $errorDetailsResolver,
        ErrorLogLevelResolverInterface $errorLogLevelResolver,
        LoggerInterface $logger,
        ?array $ignoreExceptions = null,
        ?int $priority = null
    ) {
        $this->errorDetailsResolver = $errorDetailsResolver;
        $this->logger = $logger;
        $this->ignoreExceptions = $ignoreExceptions ?? [];

        parent::__construct($errorLogLevelResolver, $priority);
    }

    /**
     * @return void|bool
     */
    public function report(Throwable $throwable)
    {
        foreach ($this->ignoreExceptions as $exceptionClass) {
            if (\is_a($throwable, $exceptionClass)) {
                return;
            }
        }

        $this->logger->log(
            $this->errorLogLevelResolver->getLogLevel($throwable),
            $throwable->getMessage(),
            [
                'exception' => $this->errorDetailsResolver->resolveExtendedDetails($throwable),
            ]
        );
    }
}
