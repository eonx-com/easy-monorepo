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
        $dotenv = \method_exists(Dotenv::class, 'usePutenv')
            ? (new Dotenv())->usePutenv(true)
            : new Dotenv(true);

        $dotenv->populate($this->getEnvsAsKeyValue($envs), true);
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
