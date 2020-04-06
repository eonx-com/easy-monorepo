<?php

declare(strict_types=1);

namespace EonX\EasySsm\Services\Dotenv;

interface SsmDotenvInterface
{
    public function loadEnv(?string $path = null): void;
}
