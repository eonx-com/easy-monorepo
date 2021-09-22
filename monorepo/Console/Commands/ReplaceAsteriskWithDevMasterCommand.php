<?php
declare(strict_types=1);

namespace EonX\EasyMonorepo\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

final class ReplaceAsteriskWithDevMasterCommand extends Command
{
    protected static $defaultName = 'replace-asterisk-with-dev-master';

    protected function configure(): void
    {
        $this->addArgument('path', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filesystem = new Filesystem();
        $filename = \sprintf('%s/composer.json', $input->getArgument('path'));

        if ($filesystem->exists($filename) === false) {
            $output->writeln(\sprintf('File %s does not exist', $filename));

            return self::FAILURE;
        }

        $updatedContents = \str_replace('*', 'dev-feature/php8-the-better-way', \file_get_contents($filename));
        $updatedContents = \str_replace('"canonical": false', '"canonical": true', $updatedContents);
        $filesystem->dumpFile($filename, $updatedContents);

        $output->writeln(\sprintf('Successfully updated %s', $filename));

        return self::SUCCESS;
    }
}
