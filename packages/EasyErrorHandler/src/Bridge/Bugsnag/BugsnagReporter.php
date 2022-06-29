<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Bugsnag;

use Bugsnag\Client;
use EonX\EasyErrorHandler\Bridge\Symfony\Interfaces\BugsnagIgnoreExceptionsResolverInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorLogLevelResolverInterface;
use EonX\EasyErrorHandler\Reporters\AbstractErrorReporter;
use Monolog\Logger;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

final class BugsnagReporter extends AbstractErrorReporter
{
    private Client $bugsnag;

    /**
     * @var string[]
     */
    private ?array $ignoreExceptions;

    private ?string $ignoreExceptionsResolver;

    private ?int $threshold;

    /**
     * @param string[]|null $ignoreExceptions
     */
    public function __construct(
        Client $bugsnag,
        ErrorLogLevelResolverInterface $errorLogLevelResolver,
        ?int $threshold = null,
        ?array $ignoreExceptions = null,
        ?string $ignoreExceptionsResolver = null,
        ?int $priority = null
    ) {
        $this->bugsnag = $bugsnag;
        $this->threshold = $threshold ?? Logger::ERROR;

        $this->ignoreExceptions = $ignoreExceptions ?? [HttpExceptionInterface::class];
        $this->ignoreExceptionsResolver = $ignoreExceptionsResolver;

        parent::__construct($errorLogLevelResolver, $priority);
    }

    /**
     * @return void|bool
     */
    public function report(Throwable $throwable)
    {
        $exceptionClass = \get_class($throwable);
        if (\is_array($this->ignoreExceptions) === true) {
            foreach ($this->ignoreExceptions as $ignoreClass) {
                if (\is_a($exceptionClass, $ignoreClass, true)) {
                    return;
                }
            }
        }

        if ($this->ignoreExceptionsResolver !== null
            && \is_a($this->ignoreExceptionsResolver, BugsnagIgnoreExceptionsResolverInterface::class, true) === true
            && $this->ignoreExceptionsResolver::shouldIgnore($throwable) === true) {
            return;
        }

        $logLevel = $this->errorLogLevelResolver->getLogLevel($throwable);

        if ($logLevel >= $this->threshold) {
            $this->bugsnag->notifyException($throwable);
        }
    }
}
