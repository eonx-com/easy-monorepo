<?php

declare(strict_types=1);

namespace EonX\EasySsm\Services\Aws;

final class SsmPathResolver implements SsmPathResolverInterface
{
    public function resolvePath(?string $path = null): string
    {
        return (string)($path ?? \getenv('SSM_PATH') ?: '/');
    }
}
