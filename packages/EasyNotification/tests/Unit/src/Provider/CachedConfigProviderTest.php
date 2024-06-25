<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Tests\Unit\Provider;

use EonX\EasyNotification\Provider\CachedConfigProvider;
use EonX\EasyNotification\Tests\Stub\Provider\ConfigProviderStub;
use EonX\EasyNotification\Tests\Unit\AbstractUnitTestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

final class CachedConfigProviderTest extends AbstractUnitTestCase
{
    public function testCachePreventCallingDecoratedFinder(): void
    {
        $defaultConfig = static::$defaultConfig;
        $stub = new ConfigProviderStub($defaultConfig);
        $cacheFinder = new CachedConfigProvider(new ArrayAdapter(), $stub, 10, 'some-key');

        $cacheFinder->provide('my-api-key', $defaultConfig['externalId']);
        $cacheFinder->provide('my-api-key', $defaultConfig['externalId']);

        self::assertEquals(1, $stub->getCalled());
    }
}
