<?php
declare(strict_types=1);

namespace EonX\EasyMonorepo\ReleaseWorker;

use PharIo\Version\Version;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symplify\MonorepoBuilder\Release\Contract\ReleaseWorker\ReleaseWorkerInterface;
use UnexpectedValueException;

final readonly class PackagesListInReadmeReleaseWorker implements ReleaseWorkerInterface
{
    private const string GITHUB_URL = 'https://github.com/';

    private Filesystem $filesystem;

    public function __construct()
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

    private function getPackagesList(): iterable
    {
        $composerFiles = new Finder()
            ->in([__DIR__ . '/../../packages'])
            ->name('composer.json')
            ->sortByName();

        foreach ($composerFiles as $composerFile) {
            $packageName = \last(\explode('/', $composerFile->getPath()));
            $json = \json_decode($composerFile->getContents(), true);

            if (\is_array($json) === false) {
                throw new UnexpectedValueException('Invalid ' . $composerFile->getRealPath() . ' content.');
            }

            yield $packageName => [
                'description' => $json['description'],
                'name' => $json['name'],
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
