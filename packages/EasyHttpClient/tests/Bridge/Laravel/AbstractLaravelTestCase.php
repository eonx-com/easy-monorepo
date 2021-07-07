<?php

declare(strict_types=1);

namespace EonX\EasyHttpClient\Tests\Bridge\Laravel;

use EonX\EasyHttpClient\Bridge\Laravel\EasyHttpClientServiceProvider;
use EonX\EasyHttpClient\Tests\AbstractTestCase;
use Laravel\Lumen\Application;

abstract class AbstractLaravelTestCase extends AbstractTestCase
{
    /**
     * @var \Laravel\Lumen\Application
     */
    private $app;

    /**
     * @param null|mixed[] $config
     * @param null|string[] $providers
     */
    protected function getApp(?array $config = null, ?array $providers = null): Application
    {
        if ($this->app !== null) {
            return $this->app;
        }

        $app = new Application(__DIR__);

        if ($config !== null) {
            \config($config);
        }

        foreach ($providers ?? [] as $provider) {
            $app->register($provider);
        }

        $app->register(EasyHttpClientServiceProvider::class);
        $app->boot();

        return $this->app = $app;
    }
}
