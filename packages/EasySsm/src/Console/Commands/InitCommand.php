<?php

declare(strict_types=1);

namespace EonX\EasySsm\Console\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class InitCommand extends AbstractCommand
{
    /**
     * @var string
     */
    protected static $defaultName = 'init';

    protected function configure(): void
    {
        $this->setDescription('Initial pull content of SSM and create YAML file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $awsProfile = $this->getAwsProfile();
        $filename = $this->getFilename();
        $style = new SymfonyStyle($input, $output);

        $style->comment(\sprintf('Initiating SSM parameters for profile "%s"', $awsProfile));

        // Check if file already exists
        if ($this->filesystem->exists($filename)) {
            $style->warning(\sprintf('Parameters file "%s" already initiated, use "pull" command instead', $filename));

            return 1;
        }

        $params = $this->getRemoteParameters($style);

        $this->ssmParamsDumper->dumpParameters($filename, $params);
        $this->hashDumper->dumpHash($awsProfile, $params);

        $style->success(\sprintf('Successfully initiated parameters file "%s"', $filename));

        return 0;
    }
}
