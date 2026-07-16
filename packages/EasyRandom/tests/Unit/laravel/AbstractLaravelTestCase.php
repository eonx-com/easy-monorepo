<?php
declare(strict_types=1);

namespace EonX\EasyRandom\Tests\Unit\Laravel;

use EonX\EasyRandom\Laravel\EasyRandomServiceProvider;
use EonX\EasyRandom\Tests\Unit\AbstractUnitTestCase;
use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Foundation\Application;

abstract class AbstractLaravelTestCase extends AbstractUnitTestCase
{
    private ?Application $app = null;

    protected function getApp(?array $config = null): Application
    {
        if ($this->app !== null) {
            return $this->app;
        }

        $this->app = new Application(__DIR__);
        $this->app->instance('config', new ConfigRepository());

        if ($config !== null) {
            \config($config);
        }

        $this->app->register(EasyRandomServiceProvider::class);

        return $this->app;
    }
}
