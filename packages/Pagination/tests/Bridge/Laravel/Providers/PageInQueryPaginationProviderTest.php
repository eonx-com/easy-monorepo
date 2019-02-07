<?php
declare(strict_types=1);

namespace StepTheFkUp\Pagination\Tests\Bridge\Laravel\Providers;

use StepTheFkUp\Pagination\Bridge\Laravel\Providers\StartSizeInQueryPaginationProvider;
use StepTheFkUp\Pagination\Resolvers\StartSizeInQueryResolver;
use StepTheFkUp\Pagination\Tests\AbstractLaravelProvidersTestCase;

final class PageInQueryPaginationProviderTest extends AbstractLaravelProvidersTestCase
{
    /**
     * StartSizeAsArrayInQueryPaginationProvider should return an instantiate of StartSizeAsArrayInQueryResolver as resolver.
     *
     * @return void
     *
     * @throws \ReflectionException
     */
    public function testResolverClosureReturnPageAsArrayInQuery(): void
    {
        $app = $this->mockApp();

        $getClosure = $this->getMethodAsPublic(
            StartSizeInQueryPaginationProvider::class,
            'getResolverClosure'
        );

        /** @var \Illuminate\Contracts\Foundation\Application $app */
        $closure = $getClosure->invoke(new StartSizeInQueryPaginationProvider($app));

        self::assertInstanceOf(StartSizeInQueryResolver::class, $closure());
    }
}
