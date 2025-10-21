<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Tests\Unit\Provider;

use EonX\EasyNotification\Provider\CachedConfigProvider;
use EonX\EasyNotification\Tests\Stub\Provider\ConfigProviderStub;
use EonX\EasyNotification\Tests\Unit\AbstractUnitTestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

final class CachedConfigProviderTest extends AbstractUnitTestCase
{
    public function testCachePreventCallingDecoratedProvider(): void
    {
        $defaultConfig = static::$defaultConfig;
        $stub = new ConfigProviderStub($defaultConfig);
        $cachedConfigProvider = new CachedConfigProvider(new ArrayAdapter(), $stub, 10, 'some-key');

        $cachedConfigProvider->provide('my-api-key', $defaultConfig['externalId']);
        $cachedConfigProvider->provide('my-api-key', $defaultConfig['externalId']);
        $cachedConfigProvider->provide('my-api-key-1', $defaultConfig['externalId']);

        self::assertEquals(2, $stub->getCalled());
    }
}
