<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Tests\Unit\Laravel;

use EonX\EasyPagination\Laravel\EasyPaginationServiceProvider;
use EonX\EasyPagination\Tests\Unit\AbstractUnitTestCase;
use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Foundation\Application;

abstract class AbstractLaravelTestCase extends AbstractUnitTestCase
{
    private ?Application $app = null;

    /**
     * @param class-string $concrete
     * @param class-string $abstract
     */
    protected function assertInstanceInApp(string $concrete, string $abstract): void
    {
        self::assertInstanceOf($concrete, $this->getApplication()->get($abstract));
    }

    protected function getApplication(?bool $pretendInConsole = null, ?array $config = null): Application
    {
        if ($this->app !== null) {
            return $this->app;
        }

        $this->app = $this->createApplication($pretendInConsole);
        $this->app->instance('config', new ConfigRepository());

        if ($config !== null) {
            $this->app->make('config')
                ->set($config);
        }

        $this->app->register(EasyPaginationServiceProvider::class);

        return $this->app;
    }

    private function createApplication(?bool $pretendInConsole = null): Application
    {
        return new class (__DIR__, $pretendInConsole) extends Application {
            public function __construct(
                ?string $basePath = null,
                private readonly ?bool $runningInConsole = null,
            ) {
                parent::__construct($basePath);
            }

            public function runningInConsole(): bool
            {
                return $this->runningInConsole ?? false;
            }
        };
    }
}
