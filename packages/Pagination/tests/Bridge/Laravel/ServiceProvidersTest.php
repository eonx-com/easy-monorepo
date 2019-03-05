<?php
declare(strict_types=1);

namespace StepTheFkUp\Pagination\Tests\Bridge\Laravel;

use StepTheFkUp\Pagination\Bridge\Laravel\Providers\StartSizeAsArrayInQueryPaginationProvider;
use StepTheFkUp\Pagination\Bridge\Laravel\Providers\StartSizeInQueryPaginationProvider;
use StepTheFkUp\Pagination\Data\StartSizeData;
use StepTheFkUp\Pagination\Interfaces\StartSizeDataInterface;
use StepTheFkUp\Pagination\Interfaces\StartSizeDataResolverInterface;
use StepTheFkUp\Pagination\Resolvers\StartSizeAsArrayInQueryResolver;
use StepTheFkUp\Pagination\Resolvers\StartSizeInQueryResolver;
use StepTheFkUp\Pagination\Tests\AbstractTestCase;

final class ServiceProvidersTest extends AbstractTestCase
{
    private static $providers = [
        StartSizeAsArrayInQueryPaginationProvider::class => StartSizeAsArrayInQueryResolver::class,
        StartSizeInQueryPaginationProvider::class => StartSizeInQueryResolver::class
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
