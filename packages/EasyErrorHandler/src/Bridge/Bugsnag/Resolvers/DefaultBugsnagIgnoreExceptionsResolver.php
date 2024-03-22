<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Bugsnag\Resolvers;

use EonX\EasyErrorHandler\Bridge\Bugsnag\Interfaces\BugsnagIgnoreExceptionsResolverInterface;
use EonX\EasyErrorHandler\Bridge\Symfony\Provider\ApiPlatformErrorResponseBuilderProvider;
use Symfony\Component\HttpFoundation\Exception\RequestExceptionInterface;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

final class DefaultBugsnagIgnoreExceptionsResolver implements BugsnagIgnoreExceptionsResolverInterface
{
    /**
     * @var class-string[]
     */
    private readonly array $ignoredExceptions;

    /**
     * @param class-string[] $ignoredExceptions
     */
    public function __construct(
        ?array $ignoredExceptions = null,
        private readonly bool $ignoreExceptionsHandledByApiPlatformBuilders = true,
        private readonly ?ApiPlatformErrorResponseBuilderProvider $apiPlatformErrorResponseBuilderProvider = null,
    ) {
        $this->ignoredExceptions = $ignoredExceptions ?? [
            HttpExceptionInterface::class,
            RequestExceptionInterface::class,
        ];
    }

    public function shouldIgnore(Throwable $throwable): bool
    {
        foreach ($this->ignoredExceptions as $ignoreClass) {
            if (\is_a($throwable, $ignoreClass)) {
                return true;
            }
        }

        if (
            $this->ignoreExceptionsHandledByApiPlatformBuilders
            && $this->apiPlatformErrorResponseBuilderProvider !== null
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
