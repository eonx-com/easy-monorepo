<?php
declare(strict_types=1);

namespace Test\Architecture\Laravel;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Finder\Finder;
use Symfony\Component\String\Inflector\EnglishInflector;
use Test\Architecture\AbstractArchitectureTestCase;

final class PluralNameForDirTest extends AbstractArchitectureTestCase
{
    private const EXCLUDE_DIRS = [
        'config',
        'translations',
    ];

    private const SKIP_DIRS = [
        '/Command',
        '/Dispatcher',
        '/Enum',
        '/ExceptionHandler',
        '/Middleware',
        '/Translator',
    ];

    #[DataProvider('providePackage')]
    public function testItSucceeds(string $baseNamespace, string $path): void
    {
        $laravelFinder = new Finder();
        $laravelFinder->directories()
            ->name('laravel')
            ->depth(0)
            ->in($path);

        if ($laravelFinder->count() === 0) {
            self::markTestSkipped('No laravel directory found');
        }

        $finder = new Finder();
        $finder->directories()
            ->exclude(self::EXCLUDE_DIRS)
            ->in($path . '/laravel');

        foreach ($finder as $dir) {
            if (self::shouldSkip($dir->getRealPath())) {
                continue;
            }

            if (self::isSingular($dir->getBasename())) {
                self::fail(\sprintf(
                    'Found singular directory name "%s" in "%s"',
                    $dir->getBasename(),
                    $dir->getRealPath()
                ));
            }
        }

        self::assertTrue(true);
    }

    private static function isSingular(string $dirName): bool
    {
        $inflector = new EnglishInflector();
        $singularDirNames = $inflector->singularize($dirName);

        return \count($singularDirNames) === 1 && $singularDirNames[0] === $dirName;
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
