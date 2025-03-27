<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Unit\Laravel;

use EonX\EasyServerless\Tests\Unit\AbstractUnitTestCase;
use Laravel\Lumen\Application;

abstract class AbstractLumenTestCase extends AbstractUnitTestCase
{
    private ?Application $app = null;

    /**
     * @param string[]|null $providers
     */
    protected function getApplication(?array $providers = null, ?array $config = null): Application
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
