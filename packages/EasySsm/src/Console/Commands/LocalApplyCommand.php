<?php

declare(strict_types=1);

namespace EonX\EasySsm\Console\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class LocalApplyCommand extends AbstractCommand
{
    /**
     * @var string
     */
    protected static $defaultName = 'local-apply';

    protected function configure(): void
    {
        $this->setDescription('Display local diff between old and local SSM parameters');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $awsProfile = $this->getAwsProfile();
        $filename = $this->getFilename();
        $oldFilename = $this->getOldFilename();
        $style = new SymfonyStyle($input, $output);

        $style->comment(\sprintf('Apply local Diff SSM parameters for profile "%s"', $awsProfile));

        if ($this->filesystem->exists($oldFilename) === false) {
            $style->warning(\sprintf('Old parameters "%s" doesn\'t exist', $oldFilename));

            return 1;
        }

        $local = $this->ssmParametersParser->parseParameters($filename);
        $old = $this->ssmParametersParser->parseParameters($oldFilename);
        $diff = $this->diffResolver->diff($local, $old);

        $applied = $this->parametersHelper->applyDiff($diff, $local);

        $this->ssmParamsDumper->dumpParameters($filename, $applied);
        $this->filesystem->remove($oldFilename);

        $this->consoleRenderer->renderDiff($diff, $local, $output);

        return 0;
    }
}
