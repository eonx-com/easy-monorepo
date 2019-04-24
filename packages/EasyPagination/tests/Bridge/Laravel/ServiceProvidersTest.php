<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyPagination\Tests\Bridge\Laravel;

use LoyaltyCorp\EasyPagination\Bridge\Laravel\Providers\StartSizeAsArrayInQueryEasyPaginationProvider;
use LoyaltyCorp\EasyPagination\Bridge\Laravel\Providers\StartSizeInQueryEasyPaginationProvider;
use LoyaltyCorp\EasyPagination\Data\StartSizeData;
use LoyaltyCorp\EasyPagination\Interfaces\StartSizeDataInterface;
use LoyaltyCorp\EasyPagination\Interfaces\StartSizeDataResolverInterface;
use LoyaltyCorp\EasyPagination\Resolvers\StartSizeAsArrayInQueryResolver;
use LoyaltyCorp\EasyPagination\Resolvers\StartSizeInQueryResolver;
use LoyaltyCorp\EasyPagination\Tests\AbstractTestCase;

final class ServiceProvidersTest extends AbstractTestCase
{
    /**
     * @var string[]
     */
    private static $providers = [
        StartSizeAsArrayInQueryEasyPaginationProvider::class => StartSizeAsArrayInQueryResolver::class,
        StartSizeInQueryEasyPaginationProvider::class => StartSizeInQueryResolver::class
    ];

    /**
     * Providers should register the expected resolvers.
     *
     * @return void
     */
    public function testRegister(): void
    {
        foreach (static::$providers as $providerClass => $resolverClass) {
            /** @var \Illuminate\Contracts\Foundation\Application $app */
            $app = $this->getApplication();
            /** @var \Illuminate\Support\ServiceProvider $provider */
            $provider = new $providerClass($app);

            $provider->boot();
            $provider->register();

            $this->assertInstanceInApp(StartSizeData::class, StartSizeDataInterface::class);
            $this->assertInstanceInApp($resolverClass, StartSizeDataResolverInterface::class);
        }
    }
}

\class_alias(
    ServiceProvidersTest::class,
    'StepTheFkUp\EasyPagination\Tests\Bridge\Laravel\ServiceProvidersTest',
    false
);
