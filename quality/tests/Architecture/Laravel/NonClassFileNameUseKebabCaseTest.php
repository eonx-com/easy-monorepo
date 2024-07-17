<?php
declare(strict_types=1);

namespace Test\Architecture\Laravel;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Test\Architecture\AbstractArchitectureTestCase;

final class NonClassFileNameUseKebabCaseTest extends AbstractArchitectureTestCase
{
    private const EXCLUDE_DIRS = [
        'bundle',
        'docs',
        'tests',
    ];

    private const SKIP_FILE_NAMES = [

    ];

    #[DataProvider('providePackage')]
    public function testItSucceeds(string $baseNamespace, string $path): void
    {
        $finder = new Finder();
        $finder->files()
            ->exclude(self::EXCLUDE_DIRS)
            ->in($path);

        foreach ($finder as $file) {
            if (self::shouldSkip($file)) {
                continue;
            }

            if (\preg_match('/^[\w]+(-[\w]+)*$/', $file->getFilenameWithoutExtension()) !== 1) {
                self::fail(\sprintf(
                    'Found non-kebab case file name "%s" in "%s"',
                    $file->getBasename(),
                    $file->getRealPath()
                ));
            }
        }

        self::assertTrue(true);
    }

    private static function shouldSkip(SplFileInfo $file): bool
    {
        foreach (self::SKIP_FILE_NAMES as $skipFileName) {
            if (\str_ends_with($file->getRealPath(), $skipFileName)) {
                return true;
            }
        }

        if ($file->getExtension() === 'php') {
            foreach (['class ', 'trait ', 'interface ', 'enum '] as $keyword) {
                if (\str_contains($file->getContents(), $keyword)) {
                    return true;
                }
            }
        }

        return false;
    }
}
