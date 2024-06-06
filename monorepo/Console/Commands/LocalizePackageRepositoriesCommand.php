<?php
declare(strict_types=1);

namespace EonX\EasyMonorepo\Console\Commands;

use EonX\EasyMonorepo\Git\GitManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

#[AsCommand(
    name: 'localize-monorepo-packages'
)]
final class LocalizePackageRepositoriesCommand extends Command
{
    public function __construct(
        private GitManager $gitManager,
    ) {
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
                foreach (\array_keys($composerJsonFileContents[$section] ?? []) as $package) {
                    if (\in_array($package, $monorepoPackageNames, true)) {
                        $composerJsonFileContents[$section][$package] = $devVersion;
                    }
                }
            }

            $composerJsonFileContents['repositories'] = $repositories;
            $composerJsonFileContents['minimum-stability'] = 'dev';
            $composerJsonFileContents['prefer-stable'] = true;
            $composerJsonFileContents['conflict'] = [
                'symfony/dependency-injection' => '5.3.7',
            ];

            $filesystem->dumpFile(
                $filename,
                (string)\json_encode(
                    $composerJsonFileContents,
                    \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES
                )
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

    private function getDir(SplFileInfo $composerJson): string
    {
        return \last(\explode('/', $composerJson->getRelativePath()));
    }

    /**
     * @return string[]
     */
    private function getMonorepoPackages(Finder $composerJsonFiles): array
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
