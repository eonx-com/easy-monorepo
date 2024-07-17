<?php
declare(strict_types=1);

namespace Test\Architecture\Common;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Finder\Finder;
use Test\Architecture\AbstractArchitectureTestCase;

final class NoForbiddenFileNameTest extends AbstractArchitectureTestCase
{
    private const FORBIDDEN_FILE_NAMES = [
        'Decorator.php',
        'EventListener.php',
        'EventListenerInterface.php',
        'EventSubscriber.php',
        'EventSubscriberInterface.php',
    ];

    private const SKIP_FILES = [
        '/EasyDoctrine/src/EntityEvent/Listener/EntityEventListener.php',
    ];

    #[DataProvider('providePackage')]
    public function testItSucceeds(string $baseNamespace, string $path): void
    {
        $finder = new Finder();
        $finder->files()
            ->in($path);
        foreach ($finder as $file) {
            if (self::shouldSkip($file->getRealPath())) {
                continue;
            }

            foreach (self::FORBIDDEN_FILE_NAMES as $forbiddenFileName) {
                if (\str_ends_with($file->getBasename(), $forbiddenFileName)) {
                    self::fail(\sprintf(
                        'Found forbidden file name "%s" in "%s"',
                        $forbiddenFileName,
                        $file->getRealPath()
                    ));
                }
            }
        }

        self::assertTrue(true);
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
