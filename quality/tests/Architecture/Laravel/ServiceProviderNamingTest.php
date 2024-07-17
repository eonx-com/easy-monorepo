<?php
declare(strict_types=1);

namespace Test\Architecture\Laravel;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Finder\Finder;
use Test\Architecture\AbstractArchitectureTestCase;

final class ServiceProviderNamingTest extends AbstractArchitectureTestCase
{
    #[DataProvider('providePackage')]
    public function testItSucceeds(string $baseNamespace, string $path): void
    {
        $finder = new Finder();
        $finder->directories()
            ->name('laravel')
            ->depth(0)
            ->in($path);

        if ($finder->count() === 0) {
            self::markTestSkipped('No laravel directory found');
        }

        $serviceProviderDir = \current(\iterator_to_array($finder));

        $fileFinder = new Finder();
        $fileFinder->files()
            ->in($serviceProviderDir->getRealPath())
            ->depth(0);

        if ($fileFinder->count() > 1) {
            self::fail(\sprintf(
                'Found more than one PHP file in "%s"',
                $serviceProviderDir->getRealPath()
            ));
        }

        $serviceProviderFile = \current(\iterator_to_array($fileFinder));

        if ($serviceProviderFile->getBasename() !== \basename($path) . 'ServiceProvider.php') {
            self::fail(\sprintf(
                'Service Provider file name "%s" does not match name "%s"',
                $serviceProviderFile->getBasename(),
                \basename($path) . 'ServiceProvider.php'
            ));
        }

        self::assertTrue(true);
    }
}
