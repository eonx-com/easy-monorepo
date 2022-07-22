<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Bugsnag\Resolvers;

use EonX\EasyErrorHandler\Bridge\Bugsnag\Interfaces\BugsnagIgnoreExceptionsResolverInterface;
use EonX\EasyErrorHandler\Bridge\Symfony\Builder\ApiPlatformValidationErrorResponseBuilder;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

final class DefaultBugsnagIgnoreExceptionsResolver implements BugsnagIgnoreExceptionsResolverInterface
{
    private bool $ignoreValidationErrors;

    /**
     * @var string[]
     */
    private array $ignoredExceptions;

    /**
     * @param string[]|null $ignoredExceptions
     */
    public function __construct(
        ?array $ignoredExceptions = null,
        bool $ignoreValidationErrors = null
    )
    {
        $this->ignoredExceptions = $ignoredExceptions ?? [HttpExceptionInterface::class];
        $this->ignoreValidationErrors = $ignoreValidationErrors ?? true;
    }

    public function shouldIgnore(Throwable $throwable): bool
    {
        $exceptionClass = \get_class($throwable);
        foreach ($this->ignoredExceptions as $ignoreClass) {
            if (\is_a($exceptionClass, $ignoreClass, true)) {
                return true;
            }
        }

        if($this->ignoreValidationErrors){
           return ApiPlatformValidationErrorResponseBuilder::supports($throwable);
        }

        return false;
    }
}
