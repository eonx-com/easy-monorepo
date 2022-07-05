<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Bugsnag;

use Bugsnag\Client;
use EonX\EasyErrorHandler\Interfaces\BugsnagIgnoreExceptionsResolverInterface;
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
    private array $ignoreExceptions;

    private BugsnagIgnoreExceptionsResolverInterface $ignoreExceptionsResolver;

    private ?int $threshold;

    /**
     * @param string[]|null $ignoreExceptions
     */
    public function __construct(
        Client $bugsnag,
        BugsnagIgnoreExceptionsResolverInterface $ignoreExceptionsResolver,
        ErrorLogLevelResolverInterface $errorLogLevelResolver,
        ?int $threshold = null,
        ?array $ignoreExceptions = null,
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
        foreach ($this->ignoreExceptions as $ignoreClass) {
            if (\is_a($exceptionClass, $ignoreClass, true)) {
                return;
            }
        }

        if ($this->ignoreExceptionsResolver->shouldIgnore($throwable) === true) {
            return;
        }

        $logLevel = $this->errorLogLevelResolver->getLogLevel($throwable);

        if ($logLevel >= $this->threshold) {
            $this->bugsnag->notifyException($throwable);
        }
    }
}
