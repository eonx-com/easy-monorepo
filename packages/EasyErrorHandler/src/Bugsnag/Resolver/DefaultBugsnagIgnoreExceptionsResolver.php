<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bugsnag\Resolver;

use EonX\EasyErrorHandler\ApiPlatform\Builder\ApiPlatformValidationErrorResponseBuilder;
use Symfony\Component\HttpFoundation\Exception\RequestExceptionInterface;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

final class DefaultBugsnagIgnoreExceptionsResolver implements BugsnagIgnoreExceptionsResolverInterface
{
    private readonly bool $ignoreValidationErrors;

    /**
     * @var class-string[]
     */
    private readonly array $ignoredExceptions;

    /**
     * @param class-string[] $ignoredExceptions
     */
    public function __construct(
        ?array $ignoredExceptions = null,
        ?bool $ignoreValidationErrors = null,
    ) {
        $this->ignoredExceptions = $ignoredExceptions ?? [
            HttpExceptionInterface::class,
            RequestExceptionInterface::class,
        ];
        $this->ignoreValidationErrors = $ignoreValidationErrors ?? true;
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
