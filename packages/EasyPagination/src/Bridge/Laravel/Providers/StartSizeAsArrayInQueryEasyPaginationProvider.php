<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyPagination\Bridge\Laravel\Providers;

use LoyaltyCorp\EasyPagination\Resolvers\StartSizeAsArrayInQueryResolver;

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

\class_alias(
    StartSizeAsArrayInQueryEasyPaginationProvider::class,
    'StepTheFkUp\EasyPagination\Bridge\Laravel\Providers\StartSizeAsArrayInQueryEasyPaginationProvider',
    false
);
