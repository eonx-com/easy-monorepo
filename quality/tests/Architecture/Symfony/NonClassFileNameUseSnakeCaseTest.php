<?php
declare(strict_types=1);

namespace Test\Architecture\Symfony;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Test\Architecture\AbstractArchitectureTestCase;

final class NonClassFileNameUseSnakeCaseTest extends AbstractArchitectureTestCase
{
    private const EXCLUDE_DIRS = [
        'docs',
        'laravel',
        'tests/Fixture/Parsing',
    ];

    private const SKIP_FILE_NAMES = [
        '/EasyErrorHandler/bundle/translations/EasyErrorHandlerBundle.en.php',
        '/EasyPagination/tests/Fixture/config/page_perPage_1_15.php',
        '/EasyTest/bin/easy-test',
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

            $fileName = self::trimDoubleExtension($file->getFilenameWithoutExtension());

            if (\preg_match('/^[\w]+(_[\w]+)*$/', $fileName) !== 1) {
                self::fail(\sprintf(
                    'Found non-snake case file name "%s" in "%s"',
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

    private static function trimDoubleExtension(string $fileName): string
    {
        $parts = \explode('.', $fileName);

        if (\count($parts) > 1) {
            \array_pop($parts);
        }

        return \implode('.', $parts);
    }
}
