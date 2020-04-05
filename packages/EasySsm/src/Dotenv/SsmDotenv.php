<?php

declare(strict_types=1);

namespace EonX\EasySsm\Dotenv;

use EonX\EasySsm\HttpKernel\EasySsmKernel;
use EonX\EasySsm\Services\Dotenv\SsmDotenvInterface;

final class SsmDotenv
{
    /**
     * @var \EonX\EasySsm\HttpKernel\EasySsmKernel
     */
    private $kernel;

    public function __construct(?EasySsmKernel $kernel = null)
    {
        $kernel = $kernel ?? new EasySsmKernel([__DIR__ . '/../../config/dotenv_loader.yaml']);
        $kernel->boot();

        $this->kernel = $kernel;
    }

    public function loadEnv(?string $path = null): void
    {
        $this->kernel->getContainer()->get(SsmDotenvInterface::class)->loadEnv($path);
    }
}
