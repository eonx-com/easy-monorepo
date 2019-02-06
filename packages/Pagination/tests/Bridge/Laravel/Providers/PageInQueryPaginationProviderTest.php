<?php
declare(strict_types=1);

namespace StepTheFkUp\Pagination\Tests\Bridge\Laravel\Providers;

use StepTheFkUp\Pagination\Bridge\Laravel\Providers\PageInQueryPaginationProvider;
use StepTheFkUp\Pagination\Resolvers\PageInQueryResolver;
use StepTheFkUp\Pagination\Tests\AbstractLaravelProvidersTestCase;

final class PageInQueryPaginationProviderTest extends AbstractLaravelProvidersTestCase
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
            PageInQueryPaginationProvider::class,
            'getPagePaginationResolverClosure'
        );

        /** @var \Illuminate\Contracts\Foundation\Application $app */
        $closure = $getClosure->invoke(new PageInQueryPaginationProvider($app));

        self::assertInstanceOf(PageInQueryResolver::class, $closure());
    }
}