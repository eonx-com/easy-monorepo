<?php
declare(strict_types=1);

namespace StepTheFkUp\Pagination\Tests\Bridge\Laravel\Providers\Stubs;

use Closure;
use StepTheFkUp\Pagination\Bridge\Laravel\Providers\AbstractPagePaginationProvider;

final class AbstractPagePaginationProviderStub extends AbstractPagePaginationProvider
{
    /**
     * Get closure to instantiate the implementation of PagePaginationDataResolverInterface.
     *
     * @return \Closure
     */
    protected function getPagePaginationResolverClosure(): Closure
    {
        return function () {
        };
    }
}