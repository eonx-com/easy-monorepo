<?php
declare(strict_types=1);

namespace Test\Architecture\Common;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
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

    #[DataProvider('provideSubject')]
    public function testItSucceeds(SplFileInfo $subject): void
    {
        $nestedFileFinder = new Finder()
            ->files()
            ->in($subject->getRealPath())
            ->depth(0);

        self::assertCount(
            0,
            $nestedFileFinder,
            \sprintf(
                'Directory "%s" with subdirectories contains files [%s]',
                $subject->getRealPath(),
                \implode(', ', \iterator_to_array($nestedFileFinder))
            )
        );
    }

    protected static function arrangeFinder(): Finder
    {
        return new Finder()
            ->directories()
            ->filter(static function (SplFileInfo $dir): bool {
                foreach (self::SKIP_DIRS as $skipDir) {
                    if (\str_ends_with($dir->getRealPath(), $skipDir)) {
                        return false;
                    }
                }

                $nestedDirFinder = new Finder()
                    ->directories()
                    ->in($dir->getRealPath());

                if ($nestedDirFinder->count() === 0) {
                    return false;
                }

                return true;
            });
    }
}
