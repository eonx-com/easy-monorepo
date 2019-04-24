<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyPagination\Bridge\Laravel\Providers;

use LoyaltyCorp\EasyPagination\Resolvers\StartSizeInQueryResolver;

final class StartSizeInQueryEasyPaginationProvider extends AbstractStartSizeEasyPaginationProvider
{
    /**
     * Get closure to instantiate the implementation of StartSizeDataResolverInterface.
     *
     * @return \Closure
     */
    protected function getResolverClosure(): \Closure
    {
        return function (): StartSizeInQueryResolver {
            return new StartSizeInQueryResolver($this->createConfig());
        };
    }
}

\class_alias(
    StartSizeInQueryEasyPaginationProvider::class,
    'StepTheFkUp\EasyPagination\Bridge\Laravel\Providers\StartSizeInQueryEasyPaginationProvider',
    false
);
