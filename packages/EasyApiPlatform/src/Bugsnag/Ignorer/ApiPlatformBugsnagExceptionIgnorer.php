<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Bugsnag\Ignorer;

use EonX\EasyApiPlatform\EasyErrorHandler\Provider\ApiPlatformErrorResponseBuilderProvider;
use EonX\EasyErrorHandler\Bugsnag\Ignorer\BugsnagExceptionIgnorerInterface;
use Throwable;

final class ApiPlatformBugsnagExceptionIgnorer implements BugsnagExceptionIgnorerInterface
{
    public function __construct(
        private readonly ApiPlatformErrorResponseBuilderProvider $apiPlatformErrorResponseBuilderProvider,
    ) {
    }

    public function shouldIgnore(Throwable $throwable): bool
    {
        foreach ($this->apiPlatformErrorResponseBuilderProvider->getBuilders() as $builder) {
            if ($builder->supports($throwable)) {
                return true;
            }
        }

        return false;
    }
}
