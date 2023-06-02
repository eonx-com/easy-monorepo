<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Reporters;

use EonX\EasyErrorHandler\Interfaces\ErrorDetailsResolverInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorLogLevelResolverInterface;
use Psr\Log\LoggerInterface;
use Throwable;

final class LoggerErrorReporter extends AbstractErrorReporter
{
    /**
     * @var class-string[]
     */
    private readonly array $ignoreExceptions;

    /**
     * @param null|class-string[] $ignoreExceptions
     */
    public function __construct(
        private readonly ErrorDetailsResolverInterface $errorDetailsResolver,
        ErrorLogLevelResolverInterface $errorLogLevelResolver,
        private readonly LoggerInterface $logger,
        ?array $ignoreExceptions = null,
        ?int $priority = null,
    ) {
        $this->ignoreExceptions = $ignoreExceptions ?? [];

        parent::__construct($errorLogLevelResolver, $priority);
    }

    public function report(Throwable $throwable): void
    {
        foreach ($this->ignoreExceptions as $exceptionClass) {
            if (\is_a($throwable, $exceptionClass)) {
                return;
            }
        }

        $this->logger->log(
            $this->errorLogLevelResolver->getLogLevel($throwable),
            $this->errorDetailsResolver->resolveInternalMessage($throwable),
            ['exception' => $this->errorDetailsResolver->resolveExtendedDetails($throwable)],
        );
    }
}
