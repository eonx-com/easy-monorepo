<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Common\Resolver;

final readonly class DefaultAppNameResolver implements AppNameResolverInterface
{
    public function __construct(
        private string $appNameEnvVar,
    ) {
    }

    public function resolveAppName(): ?string
    {
        $appName = \getenv($this->appNameEnvVar);

        return \is_string($appName) && $appName !== '' ? $appName : null;
    }
}
