<?php

declare(strict_types=1);

namespace EonX\EasySsm\Console\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class DiffCommand extends AbstractCommand
{
    /**
     * @var string
     */
    protected static $defaultName = 'diff';

    protected function configure(): void
    {
        $this->setDescription('Display diff between remote and local SSM parameters');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $awsProfile = $this->getAwsProfile();
        $filename = $this->getFilename();
        $style = new SymfonyStyle($input, $output);

        $style->comment(\sprintf('Diff SSM parameters for profile "%s"', $awsProfile));

        $remote = $this->getRemoteParameters($style);

        // If out of sync, abort
        if ($this->hashChecker->checkHash($awsProfile, $remote) === false) {
            $style->warning(\sprintf(
                'Your local parameters for "%s" are out of sync, use pull command',
                $awsProfile
            ));

            return 1;
        }

        $local = $this->ssmParametersParser->parseParameters($filename);
        $diff = $this->diffResolver->diff($remote, $local);

        // Diff summary
        $this->consoleRenderer->renderDiff($diff, $remote, $output);

        return 0;
    }
}
