<?php
declare(strict_types=1);

namespace StepTheFkUp\Pagination\Bridge\Laravel\Providers;

use StepTheFkUp\Pagination\Resolvers\PageInQueryResolver;

final class PageInQueryPaginationProvider extends AbstractPagePaginationProvider
{
    /**
     * Get closure to instantiate the implementation of PagePaginationDataResolverInterface.
     *
     * @return \Closure
     */
    protected function getPagePaginationResolverClosure(): \Closure
    {
        return function (): PageInQueryResolver {
            return new PageInQueryResolver($this->createPagePaginationConfig());
        };
    }
}