<?php
declare(strict_types=1);

namespace Test\Architecture\Symfony;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Test\Architecture\AbstractArchitectureTestCase;

final class BundleNamingTest extends AbstractArchitectureTestCase
{
    #[DataProvider('provideSubject')]
    public function testItSucceeds(SplFileInfo $subject): void
    {
        $fileFinder = new Finder();
        $fileFinder->files()
            ->in($subject->getRealPath())
            ->depth(0);
        $serviceProviderFile = \current(\iterator_to_array($fileFinder));
        $expectedName = \basename(\substr($subject->getRealPath(), 0, -7)) . 'Bundle.php';

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
                'Bundle file name "%s" does not match name "%s"',
                $serviceProviderFile->getBasename(),
                $expectedName
            )
        );
    }

    protected static function arrangeFinder(): Finder
    {
        return (new Finder())->directories()
            ->name('bundle')
            ->depth(0);
    }
}
