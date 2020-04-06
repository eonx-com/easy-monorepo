<?php

declare(strict_types=1);

namespace EonX\EasySsm\Console\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class PullCommand extends AbstractCommand
{
    protected function configure(): void
    {
        $this->setName('pull');
        $this->setDescription('Pull content of SSM and create YAML file');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $awsProfile = $this->getAwsProfile();
        $filename = $this->getFilename();
        $style = new SymfonyStyle($input, $output);

        if ($this->filesystem->exists($filename) === false) {
            $style->warning(\sprintf('File "%s" doesn\'t exist, use init instead', $filename));

            return 1;
        }

        $style->comment(\sprintf('Pulling SSM parameters for profile "%s"', $awsProfile));

        $remote = $this->getRemoteParameters($style);
        $local = $this->ssmParametersParser->parseParameters($filename);
        $merge = $this->parametersHelper->merge($remote, $local);

        // Local params have extra parameters
        if ($this->hashChecker->checkHashesForParams($remote, $merge) === false) {
            // Save copy of local parameters as "_old" not to loose it
            $this->ssmParamsDumper->dumpParameters($this->getOldFilename(), $local);
        }

        // Save fresh remote params locally
        $this->ssmParamsDumper->dumpParameters($filename, $remote);
        $this->hashDumper->dumpHash($awsProfile, $remote);

        return 0;
    }
}
