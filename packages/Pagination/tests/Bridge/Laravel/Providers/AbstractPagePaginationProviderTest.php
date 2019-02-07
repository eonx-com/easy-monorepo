<?php
declare(strict_types=1);

namespace StepTheFkUp\Pagination\Tests\Bridge\Laravel\Providers;

use StepTheFkUp\Pagination\Interfaces\StartSizeDataInterface;
use StepTheFkUp\Pagination\Interfaces\StartSizeDataResolverInterface;
use StepTheFkUp\Pagination\Resolvers\Config\StartSizeConfig;
use StepTheFkUp\Pagination\Resolvers\StartSizeInQueryResolver;
use StepTheFkUp\Pagination\Tests\AbstractLaravelProvidersTestCase;
use StepTheFkUp\Pagination\Tests\Bridge\Laravel\Providers\Stubs\AbstractStartSizePaginationProviderStub;

final class AbstractPagePaginationProviderTest extends AbstractLaravelProvidersTestCase
{
    /**
     * @var string[]
     */
    private static $binds = [
        StartSizeDataResolverInterface::class,
        StartSizeDataInterface::class
    ];

    /**
     * Provider should return expected data instance.
     *
     * @return void
     *
     * @throws \ReflectionException
     */
    public function testGetDataClosure(): void
    {
        $app = $this->mockApp();
        $app->shouldReceive('get')
            ->once()
            ->with(StartSizeDataResolverInterface::class)
            ->andReturn(new StartSizeInQueryResolver(new StartSizeConfig('page', 1, 'perPage', 15)));

        $getClosure = $this->getMethodAsPublic(
            AbstractStartSizePaginationProviderStub::class,
            'getDataClosure'
        );

        /** @var \Illuminate\Contracts\Foundation\Application $app */
        $closure = $getClosure->invoke(new AbstractStartSizePaginationProviderStub($app));

        self::assertInstanceOf(StartSizeDataInterface::class, $closure());
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
        (new AbstractStartSizePaginationProviderStub($app))->register();
    }
}
