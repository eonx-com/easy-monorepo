<?php
declare(strict_types=1);

namespace Test\Architecture\Common;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Finder\Finder;
use Test\Architecture\AbstractArchitectureTestCase;

final class NoNameWithAllUppercaseTest extends AbstractArchitectureTestCase
{
    #[DataProvider('providePackage')]
    public function testItSucceeds(string $baseNamespace, string $path): void
    {
        $finder = new Finder();
        $finder->in($path);

        foreach ($finder as $item) {
            if (\preg_match('/^[A-Z0-9_-]+$/', $item->getBasename())) {
                self::fail(\sprintf('Found item with all uppercase name: %s', $item->getRealPath()));
            }
        }

        self::assertTrue(true);
    }
}
