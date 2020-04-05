<?php

declare(strict_types=1);

namespace EonX\EasySsm\Services\Dotenv\Loaders;

use Symfony\Component\Console\Output\OutputInterface;

final class ConsoleOutputLoader extends AbstractEnvLoader
{
    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    private $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * @param \EonX\EasySsm\Services\Dotenv\Data\EnvData[] $envs
     */
    protected function doLoadEnv(array $envs): void
    {
        foreach ($envs as $env) {
            $this->output->writeln(\sprintf('export %s=%s', $env->getName(), $env->getValue()));
        }
    }
}
