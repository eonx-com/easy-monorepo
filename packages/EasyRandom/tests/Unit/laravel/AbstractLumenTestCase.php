<?php
declare(strict_types=1);

namespace EonX\EasyRandom\Tests\Unit\Laravel;

use EonX\EasyRandom\Laravel\EasyRandomServiceProvider;
use EonX\EasyRandom\Tests\Unit\AbstractUnitTestCase;
use Laravel\Lumen\Application;

abstract class AbstractLumenTestCase extends AbstractUnitTestCase
{
    private ?Application $app = null;

    protected function getApp(?array $config = null): Application
    {
        if ($this->app !== null) {
            return $this->app;
        }

        $this->app = new Application(__DIR__);

        if ($config !== null) {
            \config($config);
        }

        $this->app->register(EasyRandomServiceProvider::class);

        return $this->app;
    }
}
