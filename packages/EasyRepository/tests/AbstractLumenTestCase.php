<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyRepository\Tests;

use Laravel\Lumen\Application;

abstract class AbstractLumenTestCase extends AbstractTestCase
{
    /**
     * @var \Laravel\Lumen\Application
     */
    private $app;

    /**
     * Assert given abstract is an instance of concrete in the application container.
     *
     * @param string $concrete
     * @param string $abstract
     *
     * @return void
     */
    protected function assertInstanceInApp(string $concrete, string $abstract): void
    {
        self::assertInstanceOf($concrete, $this->getApplication()->get($abstract));
    }

    /**
     * Get lumen application.
     *
     * @return \Laravel\Lumen\Application
     */
    protected function getApplication(): Application
    {
        if ($this->app !== null) {
            return $this->app;
        }

        return $this->app = new Application(__DIR__);
    }
}
