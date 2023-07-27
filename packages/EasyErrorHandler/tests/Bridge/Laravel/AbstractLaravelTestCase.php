<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests\Bridge\Laravel;

use Bugsnag\Client;
use EonX\EasyErrorHandler\Bridge\Laravel\Provider\EasyErrorHandlerServiceProvider;
use EonX\EasyErrorHandler\Tests\AbstractTestCase;
use EonX\EasyErrorHandler\Tests\Stubs\BugsnagClientStub;
use Laravel\Lumen\Application;

abstract class AbstractLaravelTestCase extends AbstractTestCase
{
    private ?Application $app = null;

    protected function getApplication(?array $config = null): Application
    {
        if ($this->app !== null) {
            return $this->app;
        }

        $this->app = new Application(__DIR__);

        if ($config !== null) {
            \config($config);
        }

        $this->app->register(EasyErrorHandlerServiceProvider::class);
        $this->app->instance(Client::class, new BugsnagClientStub());

        return $this->app;
    }
}
