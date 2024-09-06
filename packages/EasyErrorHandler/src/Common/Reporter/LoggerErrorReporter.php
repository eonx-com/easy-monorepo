<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\Reporter;

use EonX\EasyErrorHandler\Common\Resolver\ErrorDetailsResolverInterface;
use EonX\EasyErrorHandler\Common\Resolver\ErrorLogLevelResolverInterface;
use Psr\Log\LoggerInterface;
use Throwable;

final class LoggerErrorReporter extends AbstractErrorReporter
{
    /**
     * @var class-string[]
     */
    private readonly array $ignoreExceptions;

    /**
     * @param class-string[]|null $ignoreExceptions
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
            \strtolower($this->errorLogLevelResolver->getLogLevel($throwable)->name),
            $this->errorDetailsResolver->resolveInternalMessage($throwable),
            [
                'exception' => $this->errorDetailsResolver->resolveExtendedDetails($throwable),
                'exception_reported_by_error_handler' => true,
            ]
        );
    }
}
