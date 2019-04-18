<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyPagination\Bridge\Laravel\Providers;

use StepTheFkUp\EasyPagination\Resolvers\StartSizeAsArrayInQueryResolver;

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
    'LoyaltyCorp\EasyPagination\Bridge\Laravel\Providers\StartSizeAsArrayInQueryEasyPaginationProvider',
    false
);
