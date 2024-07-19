<?php
declare(strict_types=1);

namespace Test\Architecture\Common;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Test\Architecture\AbstractArchitectureTestCase;

final class OnlyAllowedFileNameInHelperDirTest extends AbstractArchitectureTestCase
{
    private const ALLOWED_FILE_NAMES = [
        'Helper.php',
        'HelperStub.php',
        'HelperTest.php',
        'Interface.php',
        'InterfaceStub.php',
        'InterfaceTest.php',
        'Trait.php',
        'TraitStub.php',
        'TraitTest.php',
    ];

    #[DataProvider('provideSubject')]
    public function testItSucceeds(SplFileInfo $subject): void
    {
        self::assertTrue(
            $this->isNameAllowed($subject->getBasename()),
            \sprintf(
                'Found forbidden file name "%s" in "%s"',
                $subject->getBasename(),
                $subject->getRealPath()
            )
        );
    }

    protected static function arrangeFinder(): Finder
    {
        return (new Finder())->files()
            ->path('/\/Helper\//');
    }

    private function isNameAllowed(string $name): bool
    {
        foreach (self::ALLOWED_FILE_NAMES as $allowedFileName) {
            if (\str_ends_with($name, $allowedFileName)) {
                return true;
            }
        }

        return false;
    }
}
