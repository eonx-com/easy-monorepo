<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Tests\Bridge\Laravel;

use EonX\EasyPagination\Bridge\Laravel\Providers\EasyPaginationServiceProvider;
use EonX\EasyPagination\Tests\AbstractTestCase;
use EonX\EasyPsr7Factory\Bridge\Laravel\EasyPsr7FactoryServiceProvider;
use Laravel\Lumen\Application;

abstract class AbstractLaravelTestCase extends AbstractTestCase
{
    /**
     * @var \Laravel\Lumen\Application
     */
    private $app;

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

        $app = $this->app = $this->createApplication($pretendInConsole);
        $app->register(EasyPaginationServiceProvider::class);
        $app->register(EasyPsr7FactoryServiceProvider::class);

        return $app;
    }

    private function createApplication(?bool $pretendInConsole = null): Application
    {
        return new class(__DIR__, $pretendInConsole) extends Application {
            /**
             * @var null|bool
             */
            private $runningInConsole;

            public function __construct(?string $basePath = null, ?bool $runningInConsole = null)
            {
                parent::__construct($basePath);

                $this->runningInConsole = $runningInConsole;
            }

            public function runningInConsole()
            {
                return $this->runningInConsole ?? false;
            }
        };
    }
}
