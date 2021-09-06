<?php

declare(strict_types=1);

namespace EonX\EasySsm\Services\Aws;

final class SsmPathResolver implements SsmPathResolverInterface
{
    public function resolvePath(?string $path = null): string
    {
        /** @var string $ssmPathEnvValue */
        $ssmPathEnvValue = \getenv('SSM_PATH') ?: '/';
        return $path ?? $ssmPathEnvValue;
    }
}
