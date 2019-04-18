<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyPagination\Tests\Bridge\Laravel;

use StepTheFkUp\EasyPagination\Bridge\Laravel\Providers\StartSizeAsArrayInQueryEasyPaginationProvider;
use StepTheFkUp\EasyPagination\Bridge\Laravel\Providers\StartSizeInQueryEasyPaginationProvider;
use StepTheFkUp\EasyPagination\Data\StartSizeData;
use StepTheFkUp\EasyPagination\Interfaces\StartSizeDataInterface;
use StepTheFkUp\EasyPagination\Interfaces\StartSizeDataResolverInterface;
use StepTheFkUp\EasyPagination\Resolvers\StartSizeAsArrayInQueryResolver;
use StepTheFkUp\EasyPagination\Resolvers\StartSizeInQueryResolver;
use StepTheFkUp\EasyPagination\Tests\AbstractTestCase;

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
    'LoyaltyCorp\EasyPagination\Tests\Bridge\Laravel\ServiceProvidersTest',
    false
);
