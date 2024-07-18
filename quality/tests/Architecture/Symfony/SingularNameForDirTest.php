<?php
declare(strict_types=1);

namespace Test\Architecture\Symfony;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\String\Inflector\EnglishInflector;
use Test\Architecture\AbstractArchitectureTestCase;

final class SingularNameForDirTest extends AbstractArchitectureTestCase
{
    private const EXCLUDE_DIRS = [
        'docs',
        'laravel',
        'tests/Fixture/app/config',
    ];

    private const SKIP_DIRS = [
        'AwsRds',
        'CompilerPass',
        'bundles',
        'SwaggerUi',
        'keys',
        'ErrorCodes',
        'MessageBus',
        'Nai',
        'templates',
        'tests',
        'translations',
    ];

    public static function arrangeFinder(): Finder
    {
        return (new Finder())->directories()
            ->exclude(self::EXCLUDE_DIRS)
            ->notName(self::SKIP_DIRS);
    }

    #[DataProvider('provideSubject')]
    public function testItSucceeds(SplFileInfo $subject): void
    {
        self::assertTrue(
            self::isSingular($subject->getBasename()),
            \sprintf(
                'Found plural directory name "%s" in "%s"',
                $subject->getBasename(),
                $subject->getRealPath()
            )
        );
    }

    private static function isSingular(string $dirName): bool
    {
        $inflector = new EnglishInflector();
        $singularDirNames = $inflector->singularize($dirName);

        return \count($singularDirNames) === 1 && $singularDirNames[0] === $dirName;
    }
}
