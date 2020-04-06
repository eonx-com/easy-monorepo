<?php

declare(strict_types=1);

namespace EonX\EasySsm\Dotenv;

use EonX\EasySsm\HttpKernel\EasySsmKernel;
use EonX\EasySsm\Services\Dotenv\SsmDotenvInterface;
use Psr\Log\LoggerInterface;

final class SsmDotenv
{
    /**
     * @var \EonX\EasySsm\HttpKernel\EasySsmKernel
     */
    private $kernel;

    /**
     * @var null|\Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(?LoggerInterface $logger = null, ?EasySsmKernel $kernel = null)
    {
        $this->logger = $logger;

        $kernel = $kernel ?? new EasySsmKernel([__DIR__ . '/../../config/dotenv_loader.yaml']);
        $kernel->boot();

        $this->kernel = $kernel;
    }

    public function loadEnv(?bool $strict = null, ?string $path = null): void
    {
        $this
            ->kernel
            ->getContainer()
            ->get(SsmDotenvInterface::class)
            ->setLogger($this->logger)
            ->setStrict($strict ?? false)
            ->loadEnv($path);
    }
}
