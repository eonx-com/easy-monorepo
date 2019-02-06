<?php
declare(strict_types=1);

namespace StepTheFkUp\Pagination\Bridge\Laravel\Providers;

use StepTheFkUp\Pagination\Resolvers\PageAsArrayInQueryResolver;

final class PageAsArrayInQueryPaginationProvider extends AbstractPagePaginationProvider
{
    /**
     * Default query attribute used if app config not set.
     *
     * @var string
     */
    private static $defaultQueryAttr = 'page';

    /**
     * Get closure to instantiate the implementation of PagePaginationDataResolverInterface.
     *
     * @return \Closure
     */
    protected function getPagePaginationResolverClosure(): \Closure
    {
        return function (): PageAsArrayInQueryResolver {
            $queryAttr = $this->app->get('config')->get('pagination.array_in_query_attr', static::$defaultQueryAttr);

            return new PageAsArrayInQueryResolver($this->createPagePaginationConfig(), $queryAttr);
        };
    }
}