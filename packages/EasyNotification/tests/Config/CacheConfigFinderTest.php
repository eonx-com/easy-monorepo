<?php

declare(strict_types=1);

namespace EonX\EasyNotification\Tests\Config;

use EonX\EasyNotification\Config\CacheConfigFinder;
use EonX\EasyNotification\Tests\AbstractTestCase;
use EonX\EasyNotification\Tests\Stubs\ConfigFinderStub;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

final class CacheConfigFinderTest extends AbstractTestCase
{
    public function testCachePreventCallingDecoratedFinder(): void
    {
        $stub = new ConfigFinderStub(static::$defaultConfig);
        $cacheFinder = new CacheConfigFinder(new ArrayAdapter(), $stub);

        $cacheFinder->find();
        $cacheFinder->find();

        self::assertEquals(1, $stub->getCalled());
    }
}
