<?php
declare(strict_types=1);

namespace EonX\EasyRepository\Tests\Unit\Laravel;

use EonX\EasyRepository\Tests\Unit\AbstractUnitTestCase;
use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Foundation\Application;

abstract class AbstractLaravelTestCase extends AbstractUnitTestCase
{
    private ?Application $app = null;

    protected function assertInstanceInApp(string $concrete, string $abstract): void
    {
        self::assertInstanceOf($concrete, $this->getApplication()->get($abstract));
    }

    protected function getApplication(): Application
    {
        if ($this->app !== null) {
            return $this->app;
        }

        $this->app = new Application(__DIR__);
        $this->app->instance('config', new ConfigRepository());

        return $this->app;
    }
}
