<?php
declare(strict_types=1);

namespace Test\Architecture\Laravel;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Test\Architecture\AbstractArchitectureTestCase;

final class NonClassFileNameUseKebabCaseTest extends AbstractArchitectureTestCase
{
    private const EXCLUDE_DIRS = [
        'bundle',
        'docs',
        'tests',
    ];

    public static function arrangeFinder(): Finder
    {
        return (new Finder())->files()
            ->exclude(self::EXCLUDE_DIRS)
            ->filter(static function (SplFileInfo $file): bool {
                if (
                    $file->getExtension() === 'php'
                    && \preg_match('/(class|trait|interface|enum)\s/', $file->getContents()) === 1
                ) {
                    return false;
                }

                return true;
            });
    }

    #[DataProvider('provideSubject')]
    public function testItSucceeds(SplFileInfo $subject): void
    {
        self::assertMatchesRegularExpression(
            '/^[\w]+(-[\w]+)*$/',
            $subject->getFilenameWithoutExtension(),
            \sprintf(
                'Found non-kebab case file name "%s" in "%s"',
                $subject->getBasename(),
                $subject->getRealPath()
            )
        );
    }
}
