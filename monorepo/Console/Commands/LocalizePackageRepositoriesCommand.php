<?php
declare(strict_types=1);

namespace EonX\EasyMonorepo\Console\Commands;

use EonX\EasyMonorepo\Git\GitManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symplify\SmartFileSystem\Finder\FinderSanitizer;
use Symplify\SmartFileSystem\SmartFileInfo;

final class LocalizePackageRepositoriesCommand extends Command
{
    protected static $defaultName = 'localize-monorepo-packages';

    /**
     * @var \EonX\EasyMonorepo\Git\GitManager
     */
    private $gitManager;

    public function __construct(GitManager $gitManager)
    {
        $this->gitManager = $gitManager;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $composerJsonFiles = $this->getComposerJsonFiles();
        $devVersion = \sprintf('dev-%s', $this->gitManager->getCurrentBranch());
        $filesystem = new Filesystem();
        $monorepoPackages = $this->getMonorepoPackages($composerJsonFiles);
        $monorepoPackageNames = \array_keys($monorepoPackages);
        $repositories = [];

        foreach ($monorepoPackages as $dir) {
            $repositories[] = [
                'type' => 'path',
                'url' => \sprintf('../%s', $dir),
            ];
        }

        foreach ($composerJsonFiles as $composerJsonFile) {
            $filename = $composerJsonFile->getRealPath();
            $composerJsonFileContents = $this->getComposerJsonFileContents($composerJsonFile);

            // Replace monorepo packages version with dev one
            foreach (['require', 'require-dev'] as $section) {
                foreach (\array_keys($composerJsonFileContents[$section]) as $package) {
                    if (\in_array($package, $monorepoPackageNames, true)) {
                        $composerJsonFileContents[$section][$package] = $devVersion;
                    }
                }
            }

            $composerJsonFileContents['repositories'] = $repositories;
            $composerJsonFileContents['minimum-stability'] = 'dev';
            $composerJsonFileContents['prefer-stable'] = true;

            $filesystem->dumpFile($filename, \json_encode($composerJsonFileContents, \JSON_PRETTY_PRINT));

            $output->writeln(\sprintf('Successfully updated %s', $filename));
        }

        return self::SUCCESS;
    }

    private function getComposerJsonFileContents(SmartFileInfo $composerJsonFile): array
    {
        return \json_decode($composerJsonFile->getContents(), true);
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

    private function getDir(SmartFileInfo $composerJson): string
    {
        return \last(\explode('/', $composerJson->getRelativeDirectoryPath()));
    }

    /**
     * @param \Symplify\SmartFileSystem\SmartFileInfo[] $composerJsonFiles
     *
     * @return string[]
     */
    private function getMonorepoPackages(array $composerJsonFiles): array
    {
        $packages = [];

        foreach ($composerJsonFiles as $composerJsonFile) {
            $composerJsonContents = $this->getComposerJsonFileContents($composerJsonFile);

            $packages[$composerJsonContents['name']] = $this->getDir($composerJsonFile);
        }

        \ksort($packages);

        return $packages;
    }
}
