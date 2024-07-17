<?php
declare(strict_types=1);

namespace Test\Architecture;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;

abstract class AbstractArchitectureTestCase extends TestCase
{
    public static function providePackage(): iterable
    {
        yield 'Monorepo core' => [
            'baseNamespace' => 'EonX\EasyMonorepo',
            'path' => \realpath(__DIR__ . '/../../../monorepo'),
        ];

        $finder = new Finder();
        foreach ($finder->directories()->depth(0)->in(__DIR__ . '/../../../packages') as $dir) {
            yield $dir->getBasename() => [
                'baseNamespace' => 'EonX\\' . $dir->getBasename(),
                'path' => $dir->getRealPath(),
            ];
        }
    }
}
