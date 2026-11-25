<?php
declare(strict_types=1);

namespace Test\Architecture\Symfony;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Test\Architecture\AbstractArchitectureTestCase;

final class OnlyAllowedDirNameInBundleDirTest extends AbstractArchitectureTestCase
{
    private const array ALLOWED_DIR_NAMES = [
        'CompilerPass',
        'Enum',
        'config',
        'templates',
        'translations',
    ];

    #[DataProvider('provideSubject')]
    public function testItSucceeds(SplFileInfo $subject): void
    {
        self::assertContains(
            $subject->getBasename(),
            self::ALLOWED_DIR_NAMES,
            \sprintf(
                'Found forbidden directory name "%s" in "%s", move its classes to the "src" directory',
                $subject->getBasename(),
                $subject->getRealPath()
            )
        );
    }

    protected static function arrangeFinder(): Finder
    {
        return new Finder()
            ->directories()
            ->path('/^bundle\//')
            ->depth(1);
    }
}
