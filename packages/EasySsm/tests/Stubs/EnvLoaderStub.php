<?php

declare(strict_types=1);

namespace EonX\EasySsm\Tests\Stubs;

use EonX\EasySsm\Services\Dotenv\EnvLoaderInterface;

final class EnvLoaderStub implements EnvLoaderInterface
{
    /**
     * @var mixed[]
     */
    private $envs = [];

    /**
     * @return mixed[]
     */
    public function getLoadedEnvs(): array
    {
        return $this->envs;
    }

    /**
     * @param mixed[] $envs
     */
    public function loadEnv(array $envs): void
    {
        $this->envs = $envs;
    }
}
