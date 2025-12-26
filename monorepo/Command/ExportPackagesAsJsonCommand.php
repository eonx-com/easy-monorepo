<?php
declare(strict_types=1);

namespace EonX\EasyMonorepo\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use UnexpectedValueException;

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
            ->in([__DIR__ . '/../../packages'])
            ->name('composer.json');
    }

    private function getDir(SplFileInfo $composerJson): string
    {
        /** @var string $dir */
        $dir = \last(\explode('/', $composerJson->getRelativePath()));

        return $dir;
    }

    private function getRepoShortname(SplFileInfo $composerJson): string
    {
        $json = \json_decode($composerJson->getContents(), true);

        if (\is_array($json) === false) {
            throw new UnexpectedValueException('Invalid ' . $composerJson->getRealPath() . ' content.');
        }

        if (isset($json['name']) === false || \is_string($json['name']) === false) {
            throw new UnexpectedValueException(
                'Missing or invalid `name` in ' . $composerJson->getRealPath() . ' content.'
            );
        }

        return \str_replace('eonx-com/', '', $json['name']);
    }
}
