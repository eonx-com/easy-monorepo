<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Tests\Unit\Laravel;

use EonX\EasyLogging\Laravel\EasyLoggingServiceProvider;
use EonX\EasyLogging\Tests\Unit\AbstractUnitTestCase;
use EonX\EasyUtils\Laravel\EasyUtilsServiceProvider;
use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Foundation\Application;

abstract class AbstractLaravelTestCase extends AbstractUnitTestCase
{
    private ?Application $app = null;

    /**
     * The EasyUtils provider is registered by default, as the sensitive data sanitizer is enabled by default
     *
     * @param class-string<\Illuminate\Support\ServiceProvider>[]|null $providers
     */
    protected function getApp(?array $config = null, ?array $providers = null): Application
    {
        if ($this->app !== null) {
            return $this->app;
        }

        $this->app = new Application(__DIR__);
        $this->app->instance('config', new ConfigRepository());

        if ($config !== null) {
            \config($config);
        }

        foreach ($providers ?? [EasyUtilsServiceProvider::class] as $provider) {
            $this->app->register($provider);
        }

        $this->app->register(EasyLoggingServiceProvider::class);
        $this->app->boot();

        return $this->app;
    }
}
