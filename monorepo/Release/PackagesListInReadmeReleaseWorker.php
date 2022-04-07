<?php

declare(strict_types=1);

namespace EonX\EasyMonorepo\Release;

use MonorepoBuilder20220316\Symplify\SmartFileSystem\Finder\FinderSanitizer;
use PharIo\Version\Version;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symplify\MonorepoBuilder\Release\Contract\ReleaseWorker\ReleaseWorkerInterface;

final class PackagesListInReadmeReleaseWorker implements ReleaseWorkerInterface
{
    private const GITHUB_URL = 'https://github.com/';

    private Filesystem $filesystem;

    public function __construct(private FinderSanitizer $finderSanitizer)
    {
        $this->filesystem = new Filesystem();
    }

    public function getDescription(Version $version): string
    {
        return 'Keep list of packages in readme.md up-to-date';
    }

    public function work(Version $version): void
    {
        $packages = $this->getPackagesList();
        $contents = \PHP_EOL;

        foreach ($packages as $folder => $package) {
            $contents .= \sprintf(
                '- [%s](%s%s): %s' . \PHP_EOL,
                $folder,
                self::GITHUB_URL,
                $package['name'],
                $package['description'],
            );
        }

        $this->replaceContentsInReadme($contents);
    }

    /**
     * @return iterable<mixed>
     */
    private function getPackagesList(): iterable
    {
        $composerFiles = (new Finder())
            ->in([__DIR__ . '/../../packages'])
            ->name('composer.json')
            ->sortByName();

        foreach ($this->finderSanitizer->sanitize($composerFiles) as $composerFile) {
            $packageName = \last(\explode('/', $composerFile->getPath()));
            $json = \json_decode($composerFile->getContents(), true);

            yield $packageName => [
                'name' => $json['name'],
                'description' => $json['description'],
            ];
        }
    }

    private function replaceBetween(string $contents, string $replacement, string $openTag, string $closeTag): string
    {
        $pos = \strpos($contents, $openTag);
        $start = $pos === false ? 0 : $pos + \strlen($openTag);

        $pos = \strpos($contents, $closeTag, $start);
        $end = $pos === false ? \strlen($contents) : $pos;

        return \substr_replace($contents, $replacement, $start, $end - $start);
    }

    private function replaceContentsInReadme(string $contents): void
    {
        $openTag = '<!-- monorepo-packages -->';
        $closeTag = '<!-- end-monorepo-packages -->';
        $filename = __DIR__ . '/../../readme.md';
        $original = (string)\file_get_contents($filename);

        $this->filesystem->dumpFile($filename, $this->replaceBetween($original, $contents, $openTag, $closeTag));
    }
}
