<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests\Bridge\Laravel;

use EonX\EasyErrorHandler\Bridge\Laravel\Provider\EasyErrorHandlerServiceProvider;
use EonX\EasyErrorHandler\Tests\AbstractTestCase;
use Laravel\Lumen\Application;

abstract class AbstractLaravelTestCase extends AbstractTestCase
{
    /**
     * @var \Laravel\Lumen\Application
     */
    private $app;

    /**
     * @param null|string[] $providers
     * @param null|mixed[] $config
     */
    protected function getApplication(?array $config = null): Application
    {
        if ($this->app !== null) {
            return $this->app;
        }

        $app = new Application(__DIR__);

        if ($config !== null) {
            \config($config);
        }

        $app->register(EasyErrorHandlerServiceProvider::class);

        return $this->app = $app;
    }
}
