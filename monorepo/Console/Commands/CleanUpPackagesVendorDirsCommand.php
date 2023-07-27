<?php
declare(strict_types=1);

namespace EonX\EasyMonorepo\Console\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symplify\SmartFileSystem\Finder\FinderSanitizer;

#[AsCommand(
    name: 'clean-up-packages-vendor-dirs'
)]
final class CleanUpPackagesVendorDirsCommand extends Command
{
    protected function configure(): void
    {
        $this->addArgument('version');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filesystem = new Filesystem();

        foreach ($this->getComposerLockFiles() as $composerLockFile) {
            $filesystem->remove($composerLockFile->getRealPath());

            $output->writeln(\sprintf('Deleted %s', $composerLockFile->getRealPath()));
        }

        foreach ($this->getVendorDirs() as $vendorDir) {
            $filesystem->remove($vendorDir->getRealPath());

            $output->writeln(\sprintf('Deleted %s', $vendorDir->getRealPath()));
        }

        return self::SUCCESS;
    }

    /**
     * @return \Symplify\SmartFileSystem\SmartFileInfo[]
     */
    private function getComposerLockFiles(): array
    {
        $finder = (new Finder())
            ->in([__DIR__ . '/../../../packages'])
            ->files()
            ->name('composer.lock');

        return (new FinderSanitizer())->sanitize($finder);
    }

    /**
     * @return \Symplify\SmartFileSystem\SmartFileInfo[]
     */
    private function getVendorDirs(): array
    {
        $finder = (new Finder())
            ->in([__DIR__ . '/../../../packages'])
            ->directories()
            ->name('vendor');

        return (new FinderSanitizer())->sanitize($finder);
    }
}
