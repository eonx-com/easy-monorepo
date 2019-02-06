<?php
declare(strict_types=1);

namespace StepTheFkUp\Pagination\Tests\Bridge\Laravel\Providers;

use StepTheFkUp\Pagination\Bridge\Laravel\Providers\PageAsArrayInQueryPaginationProvider;
use StepTheFkUp\Pagination\Resolvers\PageAsArrayInQueryResolver;
use StepTheFkUp\Pagination\Tests\AbstractLaravelProvidersTestCase;

final class PageAsArrayInQueryPaginationProviderTest extends AbstractLaravelProvidersTestCase
{
    /**
     * PageAsArrayInQueryPaginationProvider should return an instantiate of PageAsArrayInQueryResolver as resolver.
     *
     * @return void
     *
     * @throws \ReflectionException
     */
    public function testResolverClosureReturnPageAsArrayInQuery(): void
    {
        $app = $this->mockApp();

        $getClosure = $this->getMethodAsPublic(
            PageAsArrayInQueryPaginationProvider::class,
            'getPagePaginationResolverClosure'
        );

        /** @var \Illuminate\Contracts\Foundation\Application $app */
        $closure = $getClosure->invoke(new PageAsArrayInQueryPaginationProvider($app));

        self::assertInstanceOf(PageAsArrayInQueryResolver::class, $closure());
    }
}