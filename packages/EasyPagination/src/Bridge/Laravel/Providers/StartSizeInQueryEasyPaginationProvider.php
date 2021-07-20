<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Bridge\Laravel\Providers;

use EonX\EasyPagination\Resolvers\StartSizeInQueryResolver;

/**
 * @deprecated since 3.2, will be removed in 4.0. Use EasyPaginationServiceProvider instead.
 */
final class StartSizeInQueryEasyPaginationProvider extends AbstractStartSizeEasyPaginationProvider
{
    protected function getResolverClosure(): \Closure
    {
        return static function (): StartSizeInQueryResolver {
            return new StartSizeInQueryResolver(static::createConfig());
        };
    }
}
