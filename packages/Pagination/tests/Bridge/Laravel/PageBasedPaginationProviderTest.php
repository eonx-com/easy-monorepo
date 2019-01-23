<?php
declare(strict_types=1);

namespace StepTheFkUp\Pagination\Tests\Bridge\Laravel;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Mockery\MockInterface;
use StepTheFkUp\Pagination\Bridge\Laravel\PageBasedPaginationProvider;
use StepTheFkUp\Pagination\Interfaces\PageBasedPaginationDataInterface;
use StepTheFkUp\Pagination\Tests\AbstractTestCase;

class PageBasedPaginationProviderTest extends AbstractTestCase
{
    /**
     * Provider should use default
     *
     * @return void
     *
     * @throws \ReflectionException
     */
    public function testCreatePaginationDataWithDefaults(): void
    {
        /** @var \Illuminate\Contracts\Foundation\Application $app */
        $app = $this->mockApp();
        $paginationData = $this->getCreatePaginationDataClosureResult($app);

        /** @var \StepTheFkUp\Pagination\Interfaces\PageBasedPaginationDataInterface $paginationData */
        self::assertEquals(1, $paginationData->getPage());
        self::assertEquals(10, $paginationData->getPerPage());
    }

    /**
     * Provider should use custom configuration if provided.
     *
     * @return void
     *
     * @throws \ReflectionException
     */
    public function testCreatePaginationDataWithQueryAndCustomConfig(): void
    {
        $query = ['_per_page' => 100];
        $config = ['attr_per_page' => '_per_page', 'default_page' => 5];

        /** @var \Illuminate\Contracts\Foundation\Application $app */
        $app = $this->mockApp($query, $config);
        $paginationData = $this->getCreatePaginationDataClosureResult($app);

        /** @var \StepTheFkUp\Pagination\Interfaces\PageBasedPaginationDataInterface $paginationData */
        self::assertEquals(5, $paginationData->getPage());
        self::assertEquals(100, $paginationData->getPerPage());
    }

    /**
     * Provider should register the expected interface into the services container.
     *
     * @return void
     */
    public function testProviderRegisterExpectedClasses(): void
    {
        /** @var \Illuminate\Contracts\Foundation\Application $app */
        $app = $this->mock(Application::class, function (MockInterface $app): void {
            $app->shouldReceive('singleton')
                ->once()
                ->withArgs(function ($abstract, $concrete): bool {
                    self::assertEquals(PageBasedPaginationDataInterface::class, $abstract);

                    return is_string($abstract) && $concrete instanceof \Closure;
                })
                ->andReturnNull();
        });

        (new PageBasedPaginationProvider($app))->register();
    }

    /**
     * Get the result from the closure to create the pagination data.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     * @return \StepTheFkUp\Pagination\Interfaces\PageBasedPaginationDataInterface
     *
     * @throws \ReflectionException
     */
    private function getCreatePaginationDataClosureResult(Application $app): PageBasedPaginationDataInterface
    {
        $getClosure = $this->getMethodAsPublic(PageBasedPaginationProvider::class, 'getPageBasedPaginationDataClosure');
        $closure = $getClosure->invoke(new PageBasedPaginationProvider($app));

        return $closure();
    }

    /**
     * Mock Illuminate application for given query and config.
     *
     * @param mixed[]|null $query
     * @param mixed[]|null $config
     *
     * @return \Mockery\MockInterface
     */
    private function mockApp(?array $query = null, ?array $config = null): MockInterface
    {
        return $this->mock(Application::class, function (MockInterface $app) use ($query, $config): void {
            $config = new class($config ?? []) {
                private $config;

                public function __construct(array $config)
                {
                    $this->config = $config;
                }

                public function get(string $key) {
                    return $this->config;
                }
            };

            $app->shouldReceive('get')->once()->with('config')->andReturn($config);
            $app->shouldReceive('get')->once()->with('request')->andReturn(new Request($query ?? []));
        });
    }
}