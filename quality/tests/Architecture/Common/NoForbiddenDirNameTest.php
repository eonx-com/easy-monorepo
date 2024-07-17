<?php
declare(strict_types=1);

namespace Test\Architecture\Common;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Finder\Finder;
use Test\Architecture\AbstractArchitectureTestCase;

final class NoForbiddenDirNameTest extends AbstractArchitectureTestCase
{
    private const FORBIDDEN_DIR_NAMES = [
        'Bridge',
        'Handler',
        'Handlers',
        'Interface',
        'Interfaces',
        'Manager',
        'Managers',
        'Service',
        'Services',
        'Trait',
        'Traits',
        'Util',
        'Utils',
    ];

    private const SKIP_DIRS = [
        '/EasyBatch/src/Common/Manager',
        '/EasyTest/src/Common/Trait',
    ];

    #[DataProvider('providePackage')]
    public function testItSucceeds(string $baseNamespace, string $path): void
    {
        $finder = new Finder();
        $finder->directories()
            ->in($path);
        foreach ($finder as $dir) {
            if (self::shouldSkip($dir->getRealPath())) {
                continue;
            }

            foreach (self::FORBIDDEN_DIR_NAMES as $forbiddenDirName) {
                if ($dir->getBasename() === $forbiddenDirName) {
                    self::fail(\sprintf(
                        'Found forbidden directory name "%s" in "%s"',
                        $forbiddenDirName,
                        $dir->getRealPath()
                    ));
                }
            }
        }

        self::assertTrue(true);
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
