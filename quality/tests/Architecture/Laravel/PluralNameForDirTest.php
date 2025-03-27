<?php
declare(strict_types=1);

namespace Test\Architecture\Laravel;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\String\Inflector\EnglishInflector;
use Test\Architecture\AbstractArchitectureTestCase;

final class PluralNameForDirTest extends AbstractArchitectureTestCase
{
    private const EXCLUDE_DIRS = [
        'config',
        'translations',
    ];

    private const SKIP_DIRS = [
        'HttpKernel',
        'Middleware',
    ];

    #[DataProvider('provideSubject')]
    public function testItSucceeds(SplFileInfo $subject): void
    {
        self::assertTrue(
            self::isPlural($subject->getBasename()),
            \sprintf(
                'Found singular directory name "%s" in "%s"',
                $subject->getBasename(),
                $subject->getRealPath()
            )
        );
    }

    protected static function arrangeFinder(): Finder
    {
        return (new Finder())->directories()
            ->exclude(self::EXCLUDE_DIRS)
            ->path('/laravel\//')
            ->depth(1)
            ->notName(self::SKIP_DIRS);
    }

    private static function isPlural(string $dirName): bool
    {
        $inflector = new EnglishInflector();
        $singularDirNames = $inflector->singularize($dirName);

        return (\count($singularDirNames) === 1 && $singularDirNames[0] === $dirName) === false;
    }
}
