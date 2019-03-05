<?php
declare(strict_types=1);

namespace StepTheFkUp\Pagination\Bridge\Laravel\Providers;

use StepTheFkUp\Pagination\Resolvers\StartSizeAsArrayInQueryResolver;

final class StartSizeAsArrayInQueryPaginationProvider extends AbstractStartSizePaginationProvider
{
    /**
     * Default query attribute used if app config not set.
     *
     * @var string
     */
    private static $defaultQueryAttr = 'page';

    /**
     * Get closure to instantiate the implementation of StartSizeDataResolverInterface.
     *
     * @return \Closure
     */
    protected function getResolverClosure(): \Closure
    {
        return function (): StartSizeAsArrayInQueryResolver {
            $queryAttr = $this->app->make('config')->get('pagination.array_in_query_attr', static::$defaultQueryAttr);

            return new StartSizeAsArrayInQueryResolver($this->createConfig(), $queryAttr);
        };
    }
}
