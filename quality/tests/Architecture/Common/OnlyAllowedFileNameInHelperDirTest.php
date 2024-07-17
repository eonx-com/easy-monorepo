<?php
declare(strict_types=1);

namespace Test\Architecture\Common;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Finder\Finder;
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

    #[DataProvider('providePackage')]
    public function testItSucceeds(string $baseNamespace, string $path): void
    {
        $finder = new Finder();
        $finder->directories()
            ->in($path);
        foreach ($finder as $dir) {
            if ($dir->getBasename() !== 'Helper') {
                continue;
            }

            $fileFinder = new Finder();
            $fileFinder->files()
                ->in($dir->getRealPath());

            foreach ($fileFinder as $file) {
                if ($this->isNameAllowed($file->getBasename()) === false) {
                    self::fail(\sprintf(
                        'Found forbidden file name "%s" in "%s"',
                        $file->getBasename(),
                        $file->getRealPath()
                    ));
                }
            }
        }

        self::assertTrue(true);
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
