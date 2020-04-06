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
            ->setName('export-envs')
            ->setDescription('Export SSM parameters to env variables')
            ->addOption('path', null, InputOption::VALUE_OPTIONAL, 'SSM path to get the params from');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->ssmDotenv->loadEnv($input->getOption('path') ?? null);

        return 0;
    }
}
