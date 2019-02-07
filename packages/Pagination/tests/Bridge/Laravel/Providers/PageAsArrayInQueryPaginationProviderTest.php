<?php
declare(strict_types=1);

namespace StepTheFkUp\Pagination\Tests\Bridge\Laravel\Providers;

use StepTheFkUp\Pagination\Bridge\Laravel\Providers\StartSizeAsArrayInQueryPaginationProvider;
use StepTheFkUp\Pagination\Resolvers\StartSizeAsArrayInQueryResolver;
use StepTheFkUp\Pagination\Tests\AbstractLaravelProvidersTestCase;

final class PageAsArrayInQueryPaginationProviderTest extends AbstractLaravelProvidersTestCase
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
            StartSizeAsArrayInQueryPaginationProvider::class,
            'getResolverClosure'
        );

        /** @var \Illuminate\Contracts\Foundation\Application $app */
        $closure = $getClosure->invoke(new StartSizeAsArrayInQueryPaginationProvider($app));

        self::assertInstanceOf(StartSizeAsArrayInQueryResolver::class, $closure());
    }
}