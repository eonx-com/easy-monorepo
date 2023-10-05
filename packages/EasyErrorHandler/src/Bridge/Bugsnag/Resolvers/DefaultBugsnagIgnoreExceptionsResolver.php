<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Bugsnag\Resolvers;

use EonX\EasyErrorHandler\Bridge\Bugsnag\Interfaces\BugsnagIgnoreExceptionsResolverInterface;
use EonX\EasyErrorHandler\Bridge\Symfony\Provider\ApiPlatformErrorResponseBuilderProvider;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

final class DefaultBugsnagIgnoreExceptionsResolver implements BugsnagIgnoreExceptionsResolverInterface
{
    /**
     * @param class-string[] $ignoredExceptions
     */
    public function __construct(
        private readonly array $ignoredExceptions = [HttpExceptionInterface::class, RequestExceptionInterface::class],
        private readonly bool $ignoreApiPlatformBuilderErrors = true,
        private readonly ?ApiPlatformErrorResponseBuilderProvider $apiPlatformErrorResponseBuilderProvider = null,
    ) {
    }

    public function shouldIgnore(Throwable $throwable): bool
    {
        foreach ($this->ignoredExceptions as $ignoreClass) {
            if (\is_a($throwable, $ignoreClass)) {
                return true;
            }
        }

        if (
            $this->ignoreApiPlatformBuilderErrors
            && $this->apiPlatformErrorResponseBuilderProvider instanceof ApiPlatformErrorResponseBuilderProvider
        ) {
            foreach ($this->apiPlatformErrorResponseBuilderProvider->getBuilders() as $builder) {
                if ($builder->supports($throwable)) {
                    return true;
                }
            }
        }

        return false;
    }
}
