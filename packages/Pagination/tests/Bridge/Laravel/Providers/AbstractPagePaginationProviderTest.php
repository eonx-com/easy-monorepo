<?php
declare(strict_types=1);

namespace StepTheFkUp\Pagination\Tests\Bridge\Laravel\Providers;

use StepTheFkUp\Pagination\Interfaces\PagePaginationDataInterface;
use StepTheFkUp\Pagination\Interfaces\PagePaginationDataResolverInterface;
use StepTheFkUp\Pagination\Resolvers\Config\PagePaginationConfig;
use StepTheFkUp\Pagination\Resolvers\PageInQueryResolver;
use StepTheFkUp\Pagination\Tests\AbstractLaravelProvidersTestCase;
use StepTheFkUp\Pagination\Tests\Bridge\Laravel\Providers\Stubs\AbstractPagePaginationProviderStub;

final class AbstractPagePaginationProviderTest extends AbstractLaravelProvidersTestCase
{
    /**
     * @var string[]
     */
    private static $binds = [
        PagePaginationDataResolverInterface::class,
        PagePaginationDataInterface::class
    ];

    /**
     *
     *
     * @throws \ReflectionException
     */
    public function testGetPagePaginationDataClosure(): void
    {
        $app = $this->mockApp();
        $app->shouldReceive('get')
            ->once()
            ->with(PagePaginationDataResolverInterface::class)
            ->andReturn(new PageInQueryResolver(new PagePaginationConfig('page', 1, 'perPage', 15)));

        $getClosure = $this->getMethodAsPublic(
            AbstractPagePaginationProviderStub::class,
            'getPagePaginationDataClosure'
        );

        /** @var \Illuminate\Contracts\Foundation\Application $app */
        $closure = $getClosure->invoke(new AbstractPagePaginationProviderStub($app));

        self::assertInstanceOf(PagePaginationDataInterface::class, $closure());
    }

    /**
     * Provider should register the expected services.
     *
     * @return void
     */
    public function testRegisterExceptedServices(): void
    {
        $app = $this->mockApp();

        foreach (static::$binds as $bind) {
            $app->shouldReceive('bind')->once()->withArgs(function (string $abstract, $concrete) use ($bind): bool {
                self::assertEquals($bind, $abstract);
                self::assertInstanceOf(\Closure::class, $concrete);

                return true;
            });
        }

        /** @var \Illuminate\Contracts\Foundation\Application $app */
        (new AbstractPagePaginationProviderStub($app))->register();
    }
}
