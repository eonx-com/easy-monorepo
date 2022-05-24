<?php

declare(strict_types=1);

namespace EonX\EasyUtils\Tests\Bridge\Laravel;

use EonX\EasyUtils\Bridge\Laravel\EasyUtilsServiceProvider;
use Laravel\Lumen\Application;

trait LaravelTestCaseTrait
{
    private ?Application $app = null;

    /**
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

        $app->register(EasyUtilsServiceProvider::class);

        return $this->app = $app;
    }
}
