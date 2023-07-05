<?php

declare(strict_types=1);

namespace EonX\EasyCore\Tests\Bridge\Laravel\Providers;

use EonX\EasyCore\Bridge\Laravel\Providers\CachedConfigServiceProvider;
use EonX\EasyCore\Tests\AbstractTestCase;
use Laravel\Lumen\Application;

final class CachedConfigServiceProviderTest extends AbstractTestCase
{
    /**
     * @return iterable<mixed>
     *
     * @see testGetCachedConfig
     */
    public static function dataProviderGetCachedConfig(): iterable
    {
        yield 'Has cached config' => [
            __DIR__ . '/../fixtures/cached_config/has_cached_config',
            'cached-connection',
            'database.connection',
        ];

        yield 'Has no cached config' => [
            __DIR__ . '/../fixtures/cached_config/has_no_cached_config',
            'not-cached-connection',
            'database.connection',
        ];
    }

    /**
     * @param mixed $expectedConfig
     *
     * @dataProvider dataProviderGetCachedConfig
     */
    public function testGetCachedConfig(string $basePath, $expectedConfig, string $configKey): void
    {
        $app = new Application($basePath);
        $app->register(CachedConfigServiceProvider::class);

        self::assertEquals($expectedConfig, $app->get('config')->get($configKey));
    }
}
