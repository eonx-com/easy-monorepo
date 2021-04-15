<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Bridge\Laravel;

use EonX\EasyEventDispatcher\Bridge\Laravel\EasyEventDispatcherServiceProvider;
use EonX\EasyLock\Bridge\Laravel\EasyLockServiceProvider;
use EonX\EasyWebhook\Bridge\Laravel\EasyWebhookServiceProvider;
use EonX\EasyWebhook\Tests\AbstractTestCase;
use Laravel\Lumen\Application;

abstract class AbstractLumenTestCase extends AbstractTestCase
{
    /**
     * @var \Laravel\Lumen\Application
     */
    private $app;

    /**
     * @param null|string[] $providers
     * @param null|mixed[] $config
     */
    protected function getApplication(?array $providers = null, ?array $config = null): Application
    {
        if ($this->app !== null) {
            return $this->app;
        }

        $app = new Application(__DIR__);

        if ($config !== null) {
            \config($config);
        }

        $providers = \array_merge($providers ?? [], [
            EasyEventDispatcherServiceProvider::class,
            EasyLockServiceProvider::class,
            EasyWebhookServiceProvider::class,
        ]);

        foreach ($providers as $provider) {
            $app->register($provider);
        }

        $app->boot();

        return $this->app = $app;
    }
}
