<?php

declare(strict_types=1);

namespace EonX\EasySsm\Console\Commands;

use EonX\EasySsm\Services\Dotenv\SsmDotenvInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class ExportEnvsCommand extends AbstractCommand
{
    /**
     * @var string
     */
    protected static $defaultName = 'export-envs';

    /**
     * @var \EonX\EasySsm\Services\Dotenv\SsmDotenvInterface
     */
    private $ssmDotenv;

    /**
     * @required
     */
    public function setSsmDotenv(SsmDotenvInterface $ssmDotenv): void
    {
        $this->ssmDotenv = $ssmDotenv;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Export SSM parameters to env variables')
            ->addOption(
                'strict',
                null,
                InputOption::VALUE_OPTIONAL,
                'Determine if command should fail in case parameters cannot be fetched from SSM'
            )
            ->addOption('path', null, InputOption::VALUE_OPTIONAL, 'SSM path to get the params from');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Useful for local dev
        if ($this->shouldSkip()) {
            return 0;
        }

        $strict = (bool) $input->getOption('strict');
        /** @var string|null $path */
        $path = $input->getOption('path');

        $this->ssmDotenv->setStrict($strict)
            ->loadEnv($path);

        return 0;
    }

    private function shouldSkip(): bool
    {
        $name = 'EASY_SSM_SKIP';

        return isset($_ENV[$name]) || isset($_SERVER[$name]) || \getenv($name);
    }
}
