<?php
declare(strict_types=1);

namespace EonX\EasyMonorepo\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

#[AsCommand(
    name: 'clean-up-packages-vendor-dirs'
)]
final class CleanUpPackagesVendorDirsCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filesystem = new Filesystem();

        $filesystem->remove($this->getComposerLockFiles());

        $filesystem->remove($this->getVendorDirs());

        return self::SUCCESS;
    }

    private function getComposerLockFiles(): Finder
    {
        return (new Finder())
            ->in([__DIR__ . '/../../packages'])
            ->files()
            ->name('composer.lock');
    }

    private function getVendorDirs(): Finder
    {
        return (new Finder())
            ->in([__DIR__ . '/../../packages'])
            ->directories()
            ->name('vendor');
    }
}
