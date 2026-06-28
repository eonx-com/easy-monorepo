<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Tests\Unit\Laravel;

use EonX\EasyUtils\Laravel\EasyUtilsServiceProvider;
use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Foundation\Application;

trait LaravelTestCaseTrait
{
    private ?Application $app = null;

    protected function getApplication(?array $config = null): Application
    {
        if ($this->app !== null) {
            return $this->app;
        }

        $this->app = new Application(__DIR__);
        $this->app->instance('config', new ConfigRepository());

        if ($config !== null) {
            \config($config);
        }

        $this->app->register(EasyUtilsServiceProvider::class);

        return $this->app;
    }
}
