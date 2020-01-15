<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Tests\Bridge\Laravel;

use EonX\EasyPagination\Bridge\Laravel\Providers\StartSizeAsArrayInQueryEasyPaginationProvider;
use EonX\EasyPagination\Bridge\Laravel\Providers\StartSizeInQueryEasyPaginationProvider;
use EonX\EasyPagination\Data\StartSizeData;
use EonX\EasyPagination\Interfaces\StartSizeDataInterface;
use EonX\EasyPagination\Interfaces\StartSizeDataResolverInterface;
use EonX\EasyPagination\Resolvers\StartSizeAsArrayInQueryResolver;
use EonX\EasyPagination\Resolvers\StartSizeInQueryResolver;
use EonX\EasyPagination\Tests\AbstractTestCase;

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
