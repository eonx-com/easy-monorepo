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
    private const array EXCLUDE_DIRS = [
        'docs',
        'laravel',
        'tests/Fixture/app/config',
    ];

    private const array SKIP_DIRS = [
        'Aws',
        'AwsRds',
        'CompilerPass',
        'ErrorCodes',
        'MessageBus',
        'Nai',
        'OpenApi',
        'SwaggerUi',
        'bundles',
        'keys',
        'templates',
        'tests',
        'translations',
    ];

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

    protected static function arrangeFinder(): Finder
    {
        return new Finder()
->directories()
            ->exclude(self::EXCLUDE_DIRS)
            ->notName(self::SKIP_DIRS);
    }

    private static function isSingular(string $dirName): bool
    {
        $inflector = new EnglishInflector();
        $singularDirNames = $inflector->singularize($dirName);

        return \count($singularDirNames) === 1 && $singularDirNames[0] === $dirName;
    }
}
