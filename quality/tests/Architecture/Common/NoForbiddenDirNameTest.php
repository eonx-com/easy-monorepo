<?php
declare(strict_types=1);

namespace Test\Architecture\Common;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Test\Architecture\AbstractArchitectureTestCase;

final class NoForbiddenDirNameTest extends AbstractArchitectureTestCase
{
    private const array FORBIDDEN_DIR_NAMES = [
        'Bridge',
        'Handler',
        'Handlers',
        'Interface',
        'Interfaces',
        'Manager',
        'Managers',
        'Pass',
        'Service',
        'Services',
        'Trait',
        'Traits',
        'Util',
        'Utils',
    ];

    private const array SKIP_DIRS = [
        '/EasyBatch/src/Common/Manager',
        '/EasyTest/src/Common/Trait',
        '/EasyTest/src/HttpClient/Trait',
        '/EasyTest/src/EasyErrorHandler/Trait',
    ];

    #[DataProvider('provideSubject')]
    public function testItSucceeds(SplFileInfo $subject): void
    {
        self::assertNotContains(
            $subject->getBasename(),
            self::FORBIDDEN_DIR_NAMES,
            \sprintf(
                'Found forbidden directory name "%s" in "%s"',
                $subject->getBasename(),
                $subject->getRealPath()
            )
        );
    }

    protected static function arrangeFinder(): Finder
    {
        return new Finder()
->directories()
            ->filter(static function (SplFileInfo $dir): bool {
                $hasDirectoryToSkip = \array_any(
                    self::SKIP_DIRS,
                    static fn (string $skipDir): bool => \str_ends_with($dir->getRealPath(), $skipDir)
                );

                return $hasDirectoryToSkip === false;
            });
    }
}
