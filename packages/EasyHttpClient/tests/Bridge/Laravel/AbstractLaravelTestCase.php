<?php

declare(strict_types=1);

namespace EonX\EasyHttpClient\Tests\Bridge\Laravel;

use EonX\EasyHttpClient\Bridge\Laravel\EasyHttpClientServiceProvider;
use EonX\EasyHttpClient\Tests\AbstractTestCase;
use Laravel\Lumen\Application;

abstract class AbstractLaravelTestCase extends AbstractTestCase
{
    private ?Application $app = null;

    /**
     * @param string[]|null $providers
     */
    protected function getApp(?array $config = null, ?array $providers = null): Application
    {
        if ($this->app !== null) {
            return $this->app;
        }

        $this->app = new Application(__DIR__);

        if ($config !== null) {
            \config($config);
        }

        foreach ($providers ?? [] as $provider) {
            $this->app->register($provider);
        }

        $this->app->register(EasyHttpClientServiceProvider::class);
        $this->app->boot();

        return $this->app;
    }
}
