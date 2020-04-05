<?php

declare(strict_types=1);

namespace EonX\EasySsm\Dotenv;

use EonX\EasySsm\HttpKernel\EasySsmKernel;
use EonX\EasySsm\Services\Dotenv\SsmDotenvInterface;

final class SsmDotenv
{
    public function loadEnv(?string $path = null): void
    {
        $kernel = new EasySsmKernel([__DIR__ . '/../../config/dotenv_loader.yaml']);
        $kernel->boot();

        $kernel->getContainer()->get(SsmDotenvInterface::class)->loadEnv($path);
    }
}
