<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Unit\Laravel;

use EonX\EasyApiToken\Bundle\Enum\ConfigTag;
use EonX\EasyApiToken\Laravel\EasyApiTokenServiceProvider;
use EonX\EasySecurity\Laravel\EasySecurityServiceProvider;
use EonX\EasySecurity\Tests\Stub\Provider\ApiTokenDecoderProviderStub;
use EonX\EasySecurity\Tests\Unit\AbstractUnitTestCase;
use Laravel\Lumen\Application;

abstract class AbstractLumenTestCase extends AbstractUnitTestCase
{
    private ?Application $app = null;

    /**
     * @param string[]|null $providers
     */
    protected function getApplication(?array $providers = null, ?array $config = null): Application
    {
        if ($this->app !== null) {
            return $this->app;
        }

        $this->app = new Application(__DIR__);

        if ($config !== null) {
            \config($config);
        }

        $providers = \array_merge($providers ?? [], [
            EasyApiTokenServiceProvider::class,
            EasySecurityServiceProvider::class,
        ]);

        foreach ($providers as $provider) {
            $this->app->register($provider);
        }

        $this->app->singleton(ApiTokenDecoderProviderStub::class);
        $this->app->tag(ApiTokenDecoderProviderStub::class, [ConfigTag::DecoderProvider->value]);

        return $this->app;
    }
}
