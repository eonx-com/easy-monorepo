<?php
declare(strict_types=1);

namespace Test\Architecture\Common;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Test\Architecture\AbstractArchitectureTestCase;

final class NoNameWithAllUppercaseTest extends AbstractArchitectureTestCase
{
    public static function arrangeFinder(): Finder
    {
        return new Finder();
    }

    #[DataProvider('provideSubject')]
    public function testItSucceeds(SplFileInfo $subject): void
    {
        self::assertDoesNotMatchRegularExpression(
            '/^[A-Z0-9_-]+$/',
            $subject->getBasename(),
            \sprintf(
                'Found item with the all uppercase name: %s',
                $subject->getRealPath()
            )
        );
    }
}
