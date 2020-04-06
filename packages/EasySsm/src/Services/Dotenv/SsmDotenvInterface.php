<?php

declare(strict_types=1);

namespace EonX\EasySsm\Services\Dotenv;

use Psr\Log\LoggerInterface;

interface SsmDotenvInterface
{
    public function loadEnv(?string $path = null): void;

    public function setLogger(?LoggerInterface $logger = null): self;

    public function setStrict(bool $strict): self;
}
