<?php
declare(strict_types=1);

namespace Test\Architecture\Symfony;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Finder\Finder;
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
        '/AwsRds',
        '/CompilerPass',
        '/EasyApiPlatform/bundle/templates/bundles',
        '/EasyApiPlatform/bundle/templates/bundles/ApiPlatformBundle/SwaggerUi',
        '/EasyApiToken/tests/Fixture/keys',
        '/EasyErrorHandler/src/ErrorCodes',
        '/MessageBus',
        '/Parsing/Nai',
        '/templates',
        '/tests',
        '/translations',
    ];

    #[DataProvider('providePackage')]
    public function testItSucceeds(string $baseNamespace, string $path): void
    {
        $finder = new Finder();
        $finder->directories()
            ->exclude(self::EXCLUDE_DIRS)
            ->in($path);

        foreach ($finder as $dir) {
            if (self::shouldSkip($dir->getRealPath())) {
                continue;
            }

            if (self::isSingular($dir->getBasename()) === false) {
                self::fail(\sprintf(
                    'Found plural directory name "%s" in "%s"',
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
