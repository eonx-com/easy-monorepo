<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Unit\Laravel;

use EonX\EasyServerless\Tests\Unit\AbstractUnitTestCase;
use Illuminate\Cache\CacheServiceProvider;
use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Database\DatabaseServiceProvider;
use Illuminate\Events\EventServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Queue\QueueServiceProvider;

abstract class AbstractLaravelTestCase extends AbstractUnitTestCase
{
    private ?Application $app = null;

    protected function getApplication(array $config = []): Application
    {
        if ($this->app !== null) {
            return $this->app;
        }

        $this->app = new Application(__DIR__);
        $this->app->instance('config', new ConfigRepository(\array_replace([
            'database' => [
                'connections' => [
                    'sqlite' => [
                        'database' => ':memory:',
                        'driver' => 'sqlite',
                    ],
                ],
                'default' => 'sqlite',
            ],
        ], $config)));
        $this->app->register(EventServiceProvider::class);
        $this->app->register(CacheServiceProvider::class);
        $this->app->register(DatabaseServiceProvider::class);
        $this->app->register(QueueServiceProvider::class);

        return $this->app;
    }
}
