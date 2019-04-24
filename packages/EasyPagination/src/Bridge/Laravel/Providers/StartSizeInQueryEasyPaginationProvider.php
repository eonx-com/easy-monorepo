<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyPagination\Bridge\Laravel\Providers;

use StepTheFkUp\EasyPagination\Resolvers\StartSizeInQueryResolver;

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
    'LoyaltyCorp\EasyPagination\Bridge\Laravel\Providers\StartSizeInQueryEasyPaginationProvider',
    false
);
