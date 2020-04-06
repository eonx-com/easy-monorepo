<?php

declare(strict_types=1);

namespace EonX\EasySsm\Services\Dotenv;

interface EnvLoaderInterface
{
    /**
     * @param \EonX\EasySsm\Services\Dotenv\Data\EnvData[] $envs
     */
    public function loadEnv(array $envs): void;
}
