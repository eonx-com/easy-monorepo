<?php
declare(strict_types=1);

namespace EonX\EasyMonorepo\Console\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

#[AsCommand(
    name: 'export-packages'
)]
final class ExportPackagesAsJsonCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $composerFiles = $this->getComposerJsonFiles();
        $packages = [];

        foreach ($composerFiles as $composerFile) {
            $repoName = $this->getRepoShortname($composerFile);

            $packages[$repoName] = [
                'dir' => $this->getDir($composerFile),
                'repo' => $repoName,
            ];
        }

        \ksort($packages);

        // Remove keys
        $noKeys = [];
        foreach ($packages as $package) {
            $noKeys[] = $package;
        }

        $output->write((string)\json_encode($noKeys));

        return 0;
    }

    private function getComposerJsonFiles(): Finder
    {
        return (new Finder())
            ->in([__DIR__ . '/../../../packages'])
            ->name('composer.json');
    }

    private function getDir(SplFileInfo $composerJson): string
    {
        return \last(\explode('/', $composerJson->getRelativePath()));
    }

    private function getRepoShortname(SplFileInfo $composerJson): string
    {
        $json = \json_decode($composerJson->getContents(), true);

        return \str_replace('eonx-com/', '', (string)$json['name']);
    }
}
