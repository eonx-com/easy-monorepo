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
        $defaultConfig = static::$defaultConfig;
        $stub = new ConfigFinderStub($defaultConfig);
        $cacheFinder = new CacheConfigFinder(new ArrayAdapter(), $stub);

        $cacheFinder->find('my-api-key', $defaultConfig['externalId']);
        $cacheFinder->find('my-api-key', $defaultConfig['externalId']);

        self::assertEquals(1, $stub->getCalled());
    }
}
