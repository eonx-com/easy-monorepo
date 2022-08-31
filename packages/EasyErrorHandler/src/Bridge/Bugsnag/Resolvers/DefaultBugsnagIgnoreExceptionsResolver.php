<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Bugsnag\Resolvers;

use EonX\EasyErrorHandler\Bridge\Bugsnag\Interfaces\BugsnagIgnoreExceptionsResolverInterface;
use EonX\EasyErrorHandler\Bridge\Symfony\Builder\ApiPlatformValidationErrorResponseBuilder;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

final class DefaultBugsnagIgnoreExceptionsResolver implements BugsnagIgnoreExceptionsResolverInterface
{
    /**
     * @param class-string[] $ignoredExceptions
     */
    public function __construct(
        private readonly array $ignoredExceptions = [HttpExceptionInterface::class],
        private readonly bool $ignoreValidationErrors = true
    ) {
    }

    public function shouldIgnore(Throwable $throwable): bool
    {
        foreach ($this->ignoredExceptions as $ignoreClass) {
            if (\is_a($throwable, $ignoreClass)) {
                return true;
            }
        }

        if ($this->ignoreValidationErrors) {
            return ApiPlatformValidationErrorResponseBuilder::supports($throwable);
        }

        return false;
    }
}
