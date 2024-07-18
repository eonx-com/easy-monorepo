<?php
declare(strict_types=1);

namespace Test\Architecture\Common;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
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

    public static function arrangeFinder(): Finder
    {
        return (new Finder())->files()
            ->filter(static function (\SplFileInfo $file): bool {
                foreach (self::SKIP_FILES as $skipFile) {
                    if (\str_ends_with($file->getRealPath(), $skipFile)) {
                        return false;
                    }
                }

                return true;
            });
    }

    #[DataProvider('provideSubject')]
    public function testItSucceeds(SplFileInfo $subject): void
    {
        self::assertTrue(
            self::isNameAllowed($subject),
            \sprintf(
                'Found forbidden file name "%s" in "%s"',
                $subject->getBasename(),
                $subject->getRealPath()
            )
        );
    }

    private static function isNameAllowed(SplFileInfo $file): bool
    {
        foreach (self::FORBIDDEN_FILE_NAMES as $forbiddenFileName) {
            if (\str_ends_with($file->getBasename(), $forbiddenFileName)) {
                return false;
            }
        }

        return true;
    }
}
