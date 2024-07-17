<?php
declare(strict_types=1);

namespace Test\Architecture\Common;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Finder\Finder;
use Symfony\Component\String\Inflector\EnglishInflector;
use Test\Architecture\AbstractArchitectureTestCase;

final class FileNameSuffixedWithDirNameTest extends AbstractArchitectureTestCase
{
    private const ALLOWED_SUFFIXES = [
        '',
        'AwareInterface',
        'AwareTrait',
        'AwareTraitTest',
        'Interface',
        'Stub',
        'Test',
        'Trait',
    ];

    private const EXCLUDE_DIRS = [
        'bundle/config',
        'bundle/translations',
        'laravel/config',
        'laravel/translations',
        'tests/Fixture/app/config',
        'tests/Fixture/app/translations',
        'tests/Fixture/config',
    ];

    private const SKIP_DIR_NAMES = [
        'ApiResource',
        'Attribute',
        'Constraint',
        'DataTransferObject',
        'Entity',
        'Enum',
        'Function',
        'ValueObject',
        'bundle',
        'laravel',
    ];

    private const SKIP_FILES = [
        'BundleTest.php',
        'EasyApiPlatform/tests/Application/src/Twig/TemplateOverrideTest.php',
        'EasyErrorHandler/src/Common/ErrorHandler/FormatAwareInterface.php',
        'EasyLock/src/Common/Locker/ProcessWithLockTrait.php',
        'EasySecurity/src/SymfonySecurity/Voter/ProviderRestrictedInterface.php',
        'EasyTest/config/services.php',
        'EasyUtils/src/Common/Helper/HasPriorityInterface.php',
        'EasyUtils/src/Common/Helper/HasPriorityTrait.php',
        'EasyUtils/tests/Fixture/SensitiveData/DummyObject.php',
        'ServiceProviderTest.php',
        'TestCase.php',
    ];

    #[DataProvider('providePackage')]
    public function testItSucceeds(string $baseNamespace, string $path): void
    {
        $finder = new Finder();
        $finder->files()
            ->name('*.php')
            ->exclude(self::EXCLUDE_DIRS)
            ->in($path);
        foreach ($finder as $file) {
            if (self::shouldSkip($file->getRealPath())) {
                continue;
            }

            $dirName = \basename(\dirname($file->getRealPath()));

            if (\in_array($dirName, self::SKIP_DIR_NAMES, true)) {
                continue;
            }

            if (self::isNameAllowed($file->getFilenameWithoutExtension(), $dirName) === false) {
                self::fail(\sprintf(
                    'File "%s" is not suffixed with directory name "%s"',
                    $file->getRealPath(),
                    $dirName
                ));
            }
        }

        self::assertTrue(true);
    }

    private static function isNameAllowed(string $fileName, string $dirName): bool
    {
        foreach (self::ALLOWED_SUFFIXES as $suffix) {
            if (\str_ends_with($fileName, $dirName . $suffix)) {
                return true;
            }
        }

        $inflector = new EnglishInflector();
        $singularDirNames = $inflector->singularize($dirName);

        foreach ($singularDirNames as $singularDirName) {
            foreach (self::ALLOWED_SUFFIXES as $suffix) {
                if (\str_ends_with($fileName, $singularDirName . $suffix)) {
                    return true;
                }
            }
        }

        return false;
    }

    private static function shouldSkip(string $path): bool
    {
        foreach (self::SKIP_FILES as $skipFile) {
            if (\str_ends_with($path, $skipFile)) {
                return true;
            }
        }

        return false;
    }
}
