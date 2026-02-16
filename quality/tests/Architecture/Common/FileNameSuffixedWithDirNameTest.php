<?php
declare(strict_types=1);

namespace Test\Architecture\Common;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\String\Inflector\EnglishInflector;
use Test\Architecture\AbstractArchitectureTestCase;

final class FileNameSuffixedWithDirNameTest extends AbstractArchitectureTestCase
{
    private const array ALLOWED_SUFFIXES = [
        '',
        'AwareInterface',
        'AwareTrait',
        'AwareTraitTest',
        'Interface',
        'Stub',
        'Test',
        'Trait',
    ];

    private const array EXCLUDE_DIRS = [
        'bundle/config',
        'bundle/translations',
        'laravel/config',
        'laravel/routes',
        'laravel/translations',
        'tests/Fixture/app/config',
        'tests/Fixture/app/translations',
        'tests/Fixture/config',
    ];

    private const array EXCLUDE_FILE_NAMES = [
        '*BundleTest.php',
        '*ServiceProviderTest.php',
        '*TestCase.php',
    ];

    private const array SKIP_DIR_NAMES = [
        'ApiResource',
        'Attribute',
        'Constraint',
        'DataTransferObject',
        'Entity',
        'Enum',
        'Enums',
        'Function',
        'ValueObject',
        'bundle',
        'laravel',
    ];

    private const array SKIP_FILE_NAMES = [
        'EasyApiPlatform/tests/Application/src/Common/Twig/TemplateOverrideTest.php',
        'EasyErrorHandler/src/Common/ErrorHandler/FormatAwareInterface.php',
        'EasyLock/src/Common/Locker/ProcessWithLockTrait.php',
        'EasySecurity/src/SymfonySecurity/Voter/ProviderRestrictedInterface.php',
        'EasyTest/config/services.php',
        'EasyUtils/src/Common/Helper/HasPriorityInterface.php',
        'EasyUtils/src/Common/Helper/HasPriorityTrait.php',
        'EasyUtils/src/Common/Helper/StoppableInterface.php',
        'EasyUtils/src/Common/Helper/StoppableTrait.php',
        'EasyUtils/tests/Fixture/SensitiveData/DummyObject.php',
    ];

    #[DataProvider('provideSubject')]
    public function testItSucceeds(SplFileInfo $subject): void
    {
        $parentDirName = \basename(\dirname($subject->getRealPath()));

        self::assertTrue(
            self::isNameAllowed($subject->getFilenameWithoutExtension(), $parentDirName),
            \sprintf(
                'File "%s" is not suffixed with directory name "%s"',
                $subject->getRealPath(),
                $parentDirName
            )
        );
    }

    protected static function arrangeFinder(): Finder
    {
        return new Finder()
            ->files()
            ->name('*.php')
            ->notName(self::EXCLUDE_FILE_NAMES)
            ->exclude(self::EXCLUDE_DIRS)
            ->filter(static function (SplFileInfo $file): bool {
                $dirName = \basename(\dirname($file->getRealPath()));

                if (\in_array($dirName, self::SKIP_DIR_NAMES, true)) {
                    return false;
                }

                $hasFileNameToSkip = \array_any(
                    self::SKIP_FILE_NAMES,
                    static fn (string $skipFileName): bool => \str_ends_with($file->getRealPath(), $skipFileName)
                );

                return $hasFileNameToSkip === false;
            });
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
}
