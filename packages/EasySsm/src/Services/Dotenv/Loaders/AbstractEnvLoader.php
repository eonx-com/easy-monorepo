<?php

declare(strict_types=1);

namespace EonX\EasySsm\Services\Dotenv\Loaders;

use EonX\EasySsm\Services\Dotenv\Data\EnvData;
use EonX\EasySsm\Services\Dotenv\EnvLoaderInterface;

abstract class AbstractEnvLoader implements EnvLoaderInterface
{
    /**
     * @param \EonX\EasySsm\Services\Dotenv\Data\EnvData[] $envs
     */
    public function loadEnv(array $envs): void
    {
        $this->doLoadEnv(\array_filter($envs, static function ($env): bool {
            return $env instanceof EnvData;
        }));
    }

    /**
     * @param \EonX\EasySsm\Services\Dotenv\Data\EnvData[] $envs
     */
    abstract protected function doLoadEnv(array $envs): void;
}
