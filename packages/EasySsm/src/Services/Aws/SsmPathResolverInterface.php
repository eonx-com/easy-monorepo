<?php

declare(strict_types=1);

namespace EonX\EasySsm\Services\Aws;

interface SsmPathResolverInterface
{
    public function resolvePath(?string $path = null): string;
}
