<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Bridge\Laravel\Providers;

use EonX\EasyPagination\Resolvers\StartSizeAsArrayInQueryResolver;

final class StartSizeAsArrayInQueryEasyPaginationProvider extends AbstractStartSizeEasyPaginationProvider
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
