<?php
declare(strict_types=1);

namespace Test\Architecture\Laravel;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Test\Architecture\AbstractArchitectureTestCase;

final class ServiceProviderNamingTest extends AbstractArchitectureTestCase
{
    #[DataProvider('provideSubject')]
    public function testItSucceeds(SplFileInfo $subject): void
    {
        $fileFinder = new Finder();
        $fileFinder->files()
            ->in($subject->getRealPath())
            ->depth(0);
        $serviceProviderFile = \current(\iterator_to_array($fileFinder));
        $expectedName = \basename(\substr($subject->getRealPath(), 0, -8)) . 'ServiceProvider.php';

        self::assertCount(
            1,
            $fileFinder,
            \sprintf(
                'Found more than one file in "%s"',
                $subject->getRealPath()
            )
        );
        self::assertSame(
            $expectedName,
            $serviceProviderFile->getBasename(),
            \sprintf(
                'Service Provider file name "%s" does not match name "%s"',
                $serviceProviderFile->getBasename(),
                $expectedName
            )
        );
    }

    protected static function arrangeFinder(): Finder
    {
        return new Finder()
            ->directories()
            ->name('laravel')
            ->depth(0);
    }
}
