<?php
declare(strict_types=1);

namespace StepTheFkUp\Pagination\Tests\Bridge\Laravel\Providers\Stubs;

use Closure;
use StepTheFkUp\Pagination\Bridge\Laravel\Providers\AbstractStartSizePaginationProvider;

final class AbstractStartSizePaginationProviderStub extends AbstractStartSizePaginationProvider
{
    /**
     * Get closure to instantiate the implementation of StartSizeDataResolverInterface.
     *
     * @return \Closure
     */
    protected function getResolverClosure(): Closure
    {
        return function () {
        };
    }
}