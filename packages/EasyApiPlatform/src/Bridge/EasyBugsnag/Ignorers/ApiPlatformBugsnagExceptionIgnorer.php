<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Bridge\EasyBugsnag\Ignorers;

use EonX\EasyApiPlatform\Bridge\EasyErrorHandler\Providers\ApiPlatformErrorResponseBuilderProvider;
use EonX\EasyErrorHandler\Bridge\EasyBugsnag\Interfaces\BugsnagExceptionIgnorerInterface;
use Throwable;

final class ApiPlatformBugsnagExceptionIgnorer implements BugsnagExceptionIgnorerInterface
{
    public function __construct(
        private readonly ?ApiPlatformErrorResponseBuilderProvider $apiPlatformErrorResponseBuilderProvider = null,
    ) {
    }

    public function shouldIgnore(Throwable $throwable): bool
    {
        if ($this->apiPlatformErrorResponseBuilderProvider !== null) {
            foreach ($this->apiPlatformErrorResponseBuilderProvider->getBuilders() as $builder) {
                if ($builder->supports($throwable)) {
                    return true;
                }
            }
        }

        return false;
    }
}
