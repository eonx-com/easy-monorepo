<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Tests\Bridge\Laravel;

use EonX\EasyPagination\Bridge\Laravel\Providers\EasyPaginationServiceProvider;
use EonX\EasyPagination\Tests\AbstractTestCase;
use Laravel\Lumen\Application;

abstract class AbstractLaravelTestCase extends AbstractTestCase
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

    protected function getApplication(?bool $pretendInConsole = null): Application
    {
        if ($this->app !== null) {
            return $this->app;
        }

        $this->app = $this->createApplication($pretendInConsole);
        $this->app->register(EasyPaginationServiceProvider::class);

        return $this->app;
    }

    private function createApplication(?bool $pretendInConsole = null): Application
    {
        return new class(__DIR__, $pretendInConsole) extends Application {
            public function __construct(
                ?string $basePath = null,
                private ?bool $runningInConsole = null,
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
