<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Tests\Unit\Laravel;

use EonX\EasyBugsnag\Laravel\EasyBugsnagServiceProvider;
use EonX\EasyUtils\Laravel\EasyUtilsServiceProvider;
use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;

abstract class AbstractLaravelTestCase extends TestCase
{
    private ?Application $app = null;

    protected function tearDown(): void
    {
        $this->app = null;

        \restore_error_handler();
        \restore_exception_handler();

        parent::tearDown();
    }

    protected function getApp(?array $config = null): Application
    {
        if ($this->app !== null) {
            return $this->app;
        }

        $this->app = new Application(__DIR__);
        $this->app->instance('config', new ConfigRepository());
        $this->app->instance('queue', new class() {
            public function looping(callable $callback): void
            {
            }
        });
        $request = Request::create('/');
        $this->app->instance('request', $request);
        $this->app->instance(Request::class, $request);

        if ($config !== null) {
            \config($config);
        }

        $this->app->register(EasyUtilsServiceProvider::class);
        $this->app->register(EasyBugsnagServiceProvider::class);
        $this->app->boot();

        return $this->app;
    }
}
