<?php
declare(strict_types=1);

namespace Test\Architecture;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

abstract class AbstractArchitectureTestCase extends TestCase
{
    abstract public static function arrangeFinder(): Finder;

    /**
     * @return array<\Symfony\Component\Finder\SplFileInfo>
     */
    public static function provideSubject(): iterable
    {
        foreach (self::getPackagesDir() as $path) {
            $finder = static::arrangeFinder();
            $finder->in($path);

            foreach ($finder as $subject) {
                yield $subject->getRealPath() => [
                    'subject' => $subject,
                ];
            }
        }
    }

    abstract public function testItSucceeds(SplFileInfo $subject): void;

    /**
     * @return string[]
     */
    protected static function getPackagesDir(): iterable
    {
        yield \realpath(__DIR__ . '/../../../monorepo');

        $finder = new Finder();
        foreach ($finder->directories()->depth(0)->in(__DIR__ . '/../../../packages') as $dir) {
            yield $dir->getRealPath();
        }
    }
}
