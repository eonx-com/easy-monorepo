<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\Tests\Unit\Laravel;

use EonX\EasyRandom\Laravel\EasyRandomServiceProvider;
use EonX\EasyRequestId\Laravel\EasyRequestIdServiceProvider;
use EonX\EasyRequestId\Tests\Unit\AbstractUnitTestCase;
use Illuminate\Bus\BusServiceProvider;
use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Foundation\Application;

abstract class AbstractLaravelTestCase extends AbstractUnitTestCase
{
    private ?Application $app = null;

    protected function getApplication(): Application
    {
        if ($this->app !== null) {
            return $this->app;
        }

        $this->app = new Application(__DIR__);
        $this->app->instance('config', new ConfigRepository());
        $this->app->register(BusServiceProvider::class);
        $this->app->register(EasyRequestIdServiceProvider::class);
        $this->app->register(EasyRandomServiceProvider::class);

        return $this->app;
    }
}
