<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Bridge\Laravel;

use EonX\EasyApiToken\Bridge\BridgeConstantsInterface as EasyApiTokenConstantsInterface;
use EonX\EasyApiToken\Bridge\Laravel\EasyApiTokenServiceProvider;
use EonX\EasySecurity\Bridge\Laravel\EasySecurityServiceProvider;
use EonX\EasySecurity\Tests\AbstractTestCase;
use EonX\EasySecurity\Tests\Stubs\ApiTokenDecoderProviderStub;
use Laravel\Lumen\Application;

abstract class AbstractLumenTestCase extends AbstractTestCase
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
        $this->app->tag(ApiTokenDecoderProviderStub::class, [EasyApiTokenConstantsInterface::TAG_DECODER_PROVIDER]);

        return $this->app;
    }
}
