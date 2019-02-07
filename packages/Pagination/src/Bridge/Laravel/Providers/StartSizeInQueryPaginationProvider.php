<?php
declare(strict_types=1);

namespace StepTheFkUp\Pagination\Bridge\Laravel\Providers;

use StepTheFkUp\Pagination\Resolvers\StartSizeInQueryResolver;

final class StartSizeInQueryPaginationProvider extends AbstractStartSizePaginationProvider
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
