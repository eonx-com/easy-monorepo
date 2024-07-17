<?php
declare(strict_types=1);

namespace Test\Architecture\Symfony;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Finder\Finder;
use Test\Architecture\AbstractArchitectureTestCase;

final class BundleNamingTest extends AbstractArchitectureTestCase
{
    #[DataProvider('providePackage')]
    public function testItSucceeds(string $baseNamespace, string $path): void
    {
        $finder = new Finder();
        $finder->directories()
            ->name('bundle')
            ->depth(0)
            ->in($path);

        if ($finder->count() === 0) {
            self::markTestSkipped('No bundle directory found');
        }

        $bundleDir = \current(\iterator_to_array($finder));

        $fileFinder = new Finder();
        $fileFinder->files()
            ->in($bundleDir->getRealPath())
            ->depth(0);

        if ($fileFinder->count() > 1) {
            self::fail(\sprintf(
                'Found more than one PHP file in "%s"',
                $bundleDir->getRealPath()
            ));
        }

        $bundleFile = \current(\iterator_to_array($fileFinder));

        if ($bundleFile->getBasename() !== \basename($path) . 'Bundle.php') {
            self::fail(\sprintf(
                'Bundle file name "%s" does not match name "%s"',
                $bundleFile->getBasename(),
                \basename($path) . 'Bundle.php'
            ));
        }

        self::assertTrue(true);
    }
}
