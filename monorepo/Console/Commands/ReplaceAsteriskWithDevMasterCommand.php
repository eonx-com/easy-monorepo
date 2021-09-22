<?php
declare(strict_types=1);

namespace EonX\EasyMonorepo\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symplify\SmartFileSystem\Finder\FinderSanitizer;

final class ReplaceAsteriskWithDevMasterCommand extends Command
{
    protected static $defaultName = 'replace-asterisk-with-dev-master';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filesystem = new Filesystem();

        foreach ($this->getComposerJsonFiles() as $composerJsonFile) {
            $filename = $composerJsonFile->getRealPath();

            $updatedContents = \str_replace('*', 'dev-feature/php8-the-better-way', \file_get_contents($filename));
            $updatedContents = \str_replace('"canonical": false', '"canonical": true', $updatedContents);

            $filesystem->dumpFile($filename, $updatedContents);

            $output->writeln(\sprintf('Successfully updated %s', $filename));
        }

        return self::SUCCESS;
    }

    /**
     * @return \Symplify\SmartFileSystem\SmartFileInfo[]
     */
    private function getComposerJsonFiles(): array
    {
        $finder = (new Finder())
            ->in([__DIR__ . '/../../../packages'])
            ->name('composer.json');

        return (new FinderSanitizer())->sanitize($finder);
    }
}
