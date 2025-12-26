<?php
declare(strict_types=1);

namespace Test\Architecture\Symfony;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Test\Architecture\AbstractArchitectureTestCase;

final class NonClassFileNameUseSnakeCaseTest extends AbstractArchitectureTestCase
{
    private const array EXCLUDE_DIRS = [
        'docs',
        'laravel',
        'tests/Fixture/Parsing',
    ];

    private const array SKIP_FILE_NAMES = [
        '/EasyErrorHandler/bundle/translations/EasyErrorHandlerBundle.en.php',
        '/EasyPagination/tests/Fixture/config/page_perPage_1_15.php',
        '/EasyTest/bin/easy-test',
    ];

    #[DataProvider('provideSubject')]
    public function testItSucceeds(SplFileInfo $subject): void
    {
        self::assertMatchesRegularExpression(
            '/^[\w]+(_[\w]+)*$/',
            self::trimDoubleExtension($subject->getFilenameWithoutExtension()),
            \sprintf(
                'Found non-snake case file name "%s" in "%s"',
                $subject->getBasename(),
                $subject->getRealPath()
            )
        );
    }

    protected static function arrangeFinder(): Finder
    {
        return new Finder()
->files()
            ->exclude(self::EXCLUDE_DIRS)
            ->filter(static function (SplFileInfo $file): bool {
                foreach (self::SKIP_FILE_NAMES as $skipFileName) {
                    if (\str_ends_with($file->getRealPath(), $skipFileName)) {
                        return false;
                    }
                }

                if (
                    $file->getExtension() === 'php'
                    && \preg_match('/(class|trait|interface|enum)\s/', $file->getContents()) === 1
                ) {
                    return false;
                }

                return true;
            });
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
