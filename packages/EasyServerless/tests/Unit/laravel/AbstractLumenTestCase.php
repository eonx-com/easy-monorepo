<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Unit\Laravel;

use EonX\EasyServerless\Tests\Unit\AbstractUnitTestCase;
use Laravel\Lumen\Application;

abstract class AbstractLumenTestCase extends AbstractUnitTestCase
{
    private ?Application $app = null;

    protected function tearDown(): void
    {
        if ($this->app !== null) {
            // Lumen application registers its own error and exception handlers on construction
            \restore_error_handler();
            \restore_exception_handler();
            $this->app = null;
        }

        parent::tearDown();
    }

    /**
     * @param string[] $providers
     */
    protected function getApplication(array $providers, ?array $config = null): Application
    {
        if ($this->app !== null) {
            return $this->app;
        }

        $this->app = new Application(__DIR__);

        if ($config !== null) {
            \config($config);
        }

        foreach ($providers as $provider) {
            $this->app->register($provider);
        }

        return $this->app;
    }
}
