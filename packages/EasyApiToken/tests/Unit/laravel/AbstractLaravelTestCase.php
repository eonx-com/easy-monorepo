<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Tests\Unit\Laravel;

use EonX\EasyApiToken\Tests\Unit\AbstractUnitTestCase;
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
        $this->app->boot();

        return $this->app;
    }
}
