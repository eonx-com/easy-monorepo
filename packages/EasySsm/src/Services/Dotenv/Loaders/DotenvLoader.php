<?php

declare(strict_types=1);

namespace EonX\EasySsm\Services\Dotenv\Loaders;

use Symfony\Component\Dotenv\Dotenv;

final class DotenvLoader extends AbstractEnvLoader
{
    /**
     * @param \EonX\EasySsm\Services\Dotenv\Data\EnvData[] $envs
     */
    protected function doLoadEnv(array $envs): void
    {
        (new Dotenv(true))->populate($this->getEnvsAsKeyValue($envs), true);
    }

    /**
     * @param \EonX\EasySsm\Services\Dotenv\Data\EnvData[] $envs
     *
     * @return mixed[]
     */
    private function getEnvsAsKeyValue(array $envs): array
    {
        $array = [];

        foreach ($envs as $env) {
            $array[$env->getName()] = $env->getValue();
        }

        return $array;
    }
}
