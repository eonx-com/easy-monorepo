<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Unit\Laravel;

use EonX\EasyEventDispatcher\Bridge\Laravel\EasyEventDispatcherServiceProvider;
use EonX\EasyLock\Bridge\Laravel\EasyLockServiceProvider;
use EonX\EasyWebhook\Laravel\EasyWebhookServiceProvider;
use EonX\EasyWebhook\Tests\Unit\AbstractUnitTestCase;
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
            EasyEventDispatcherServiceProvider::class,
            EasyLockServiceProvider::class,
            EasyWebhookServiceProvider::class,
        ]);

        foreach ($providers as $provider) {
            $this->app->register($provider);
        }

        $this->app->boot();

        return $this->app;
    }
}
