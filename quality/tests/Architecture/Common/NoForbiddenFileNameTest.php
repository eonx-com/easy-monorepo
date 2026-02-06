<?php
declare(strict_types=1);

namespace Test\Architecture\Common;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Test\Architecture\AbstractArchitectureTestCase;

final class NoForbiddenFileNameTest extends AbstractArchitectureTestCase
{
    private const array FORBIDDEN_FILE_NAMES = [
        'Decorator.php',
        'EventListener.php',
        'EventListenerInterface.php',
        'EventSubscriber.php',
        'EventSubscriberInterface.php',
    ];

    private const array SKIP_FILES = [
        '/EasyDoctrine/src/EntityEvent/Listener/EntityEventListener.php',
    ];

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

    protected static function arrangeFinder(): Finder
    {
        return new Finder()
->files()
            ->filter(static function (\SplFileInfo $file): bool {
                $hasFileToSkip = \array_any(
                    self::SKIP_FILES,
                    static fn (string $skipFile): bool => \str_ends_with($file->getRealPath(), $skipFile)
                );

                return $hasFileToSkip === false;
            });
    }

    private static function isNameAllowed(SplFileInfo $file): bool
    {
        $hasForbiddenFileName = \array_any(
            self::FORBIDDEN_FILE_NAMES,
            static fn (string $forbiddenFileName): bool => \str_ends_with($file->getBasename(), $forbiddenFileName)
        );

        return $hasForbiddenFileName === false;
    }
}
