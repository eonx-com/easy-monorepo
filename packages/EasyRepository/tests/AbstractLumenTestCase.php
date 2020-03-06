<?php
declare(strict_types=1);

namespace EonX\EasyRepository\Tests;

use Laravel\Lumen\Application;

abstract class AbstractLumenTestCase extends AbstractTestCase
{
    /**
     * @var \Laravel\Lumen\Application
     */
    private $app;

    protected function assertInstanceInApp(string $concrete, string $abstract): void
    {
        self::assertInstanceOf($concrete, $this->getApplication()->get($abstract));
    }

    protected function getApplication(): Application
    {
        if ($this->app !== null) {
            return $this->app;
        }

        return $this->app = new Application(__DIR__);
    }
}
