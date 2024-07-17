<?php
declare(strict_types=1);

namespace Test\Architecture\Common;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Finder\Finder;
use Test\Architecture\AbstractArchitectureTestCase;

final class NoNestedFilesInDirWithSubDirTest extends AbstractArchitectureTestCase
{
    private const SKIP_DIRS = [
        '/bundle',
        '/docs',
        '/laravel',
        '/tests/Application',
        '/tests/Fixture/app/config',
        '/tests/Fixture/app/config/packages',
        '/tests/Unit',
    ];

    #[DataProvider('providePackage')]
    public function testItSucceeds(string $baseNamespace, string $path): void
    {
        $finder = new Finder();
        $finder->directories()
            ->in($path);
        foreach ($finder as $dir) {
            if (self::shouldSkip($dir->getRealPath())) {
                continue;
            }

            $nestedDirFinder = new Finder();
            $nestedDirFinder->directories()
                ->in($dir->getRealPath());
            if ($nestedDirFinder->count() > 0) {
                $nestedFileFinder = new Finder();
                $nestedFileFinder->files()
                    ->in($dir->getRealPath())
                    ->depth(0);

                if ($nestedFileFinder->count() > 0) {
                    self::fail(\sprintf(
                        'Directory "%s" with subdirectories [%s] contains files [%s]',
                        $dir->getRealPath(),
                        \implode(', ', \iterator_to_array($nestedDirFinder)),
                        \implode(', ', \iterator_to_array($nestedFileFinder))
                    ));
                }
            }
        }

        self::assertTrue(true);
    }

    private static function shouldSkip(string $path): bool
    {
        foreach (self::SKIP_DIRS as $skipDir) {
            if (\str_ends_with($path, $skipDir)) {
                return true;
            }
        }

        return false;
    }
}
