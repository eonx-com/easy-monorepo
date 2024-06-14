<?php
declare(strict_types=1);

namespace EonX\EasyMonorepo\Console\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

#[AsCommand(
    name: 'globalize-monorepo-packages'
)]
final class GlobalizePackageRepositoriesCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $composerJsonFiles = $this->getComposerJsonFiles();
        $filesystem = new Filesystem();

        foreach ($composerJsonFiles as $composerJsonFile) {
            $filename = $composerJsonFile->getRealPath();
            $composerJsonFileContents = $this->getComposerJsonFileContents($composerJsonFile);

            // Remove local path repositories
            unset(
                $composerJsonFileContents['repositories'],
                $composerJsonFileContents['minimum-stability'],
                $composerJsonFileContents['prefer-stable']
            );

            // Restore original versions from 'extra' section
            if (isset($composerJsonFileContents['extra']['original-versions'])) {
                foreach (['require', 'require-dev'] as $section) {
                    foreach ($composerJsonFileContents['extra']['original-versions'] as $package => $version) {
                        if (isset($composerJsonFileContents[$section][$package])) {
                            $composerJsonFileContents[$section][$package] = $version;
                        }
                    }
                }

                // Remove the 'extra' backup after restoration
                unset($composerJsonFileContents['extra']['original-versions']);

                if (count($composerJsonFileContents['extra']) === 0) {
                    unset($composerJsonFileContents['extra']);
                }
            }

            $filesystem->dumpFile(
                $filename,
                \json_encode(
                    $composerJsonFileContents,
                    \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES
                ) . \PHP_EOL
            );

            $output->writeln(\sprintf('Successfully updated %s', $filename));
        }

        return self::SUCCESS;
    }

    private function getComposerJsonFileContents(SplFileInfo $composerJsonFile): array
    {
        return \json_decode($composerJsonFile->getContents(), true);
    }

    private function getComposerJsonFiles(): Finder
    {
        return (new Finder())
            ->in([__DIR__ . '/../../../packages'])
            ->name('composer.json');
    }
}
