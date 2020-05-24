<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Bridge\Laravel;

use EonX\EasySecurity\Bridge\Laravel\EasySecurityServiceProvider;
use EonX\EasySecurity\Tests\AbstractTestCase;
use Laravel\Lumen\Application;

abstract class AbstractLumenTestCase extends AbstractTestCase
{
    /**
     * @var \Laravel\Lumen\Application
     */
    private $app;

    /**
     * @param null|string[] $providers
     */
    protected function getApplication(?array $providers = null): Application
    {
        if ($this->app !== null) {
            return $this->app;
        }

        $app = new Application(__DIR__);
        $app->register(EasySecurityServiceProvider::class);

        foreach ($providers ?? [] as $provider) {
            $app->register($provider);
        }

        return $this->app = $app;
    }
}
