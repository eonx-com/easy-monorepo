<?php

declare(strict_types=1);

namespace EonX\EasyBugsnag\Resolvers;

use EonX\EasyBugsnag\Interfaces\AppNameResolverInterface;

final class DefaultAppNameResolver implements AppNameResolverInterface
{
    /**
     * @var string
     */
    private $appNameEnvVar;

    public function __construct(string $appNameEnvVar)
    {
        $this->appNameEnvVar = $appNameEnvVar;
    }

    public function resolveAppName(): ?string
    {
        $appName = \getenv($this->appNameEnvVar);

        return \is_string($appName) && $appName !== '' ? $appName : null;
    }
}
