<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Bridge\Laravel\Providers;

use EonX\EasyPagination\Resolvers\StartSizeInQueryResolver;

final class StartSizeInQueryEasyPaginationProvider extends AbstractStartSizeEasyPaginationProvider
{
    protected function getResolverClosure(): \Closure
    {
        return function (): StartSizeInQueryResolver {
            return new StartSizeInQueryResolver($this->createConfig());
        };
    }
}
