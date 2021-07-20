<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Bridge\Laravel\Providers;

use EonX\EasyPagination\Resolvers\StartSizeAsArrayInQueryResolver;

/**
 * @deprecated since 3.2, will be removed in 4.0. Use EasyPaginationServiceProvider instead.
 */
final class StartSizeAsArrayInQueryEasyPaginationProvider extends AbstractStartSizeEasyPaginationProvider
{
    /**
     * @var string
     */
    private static $defaultQueryAttr = 'page';

    protected function getResolverClosure(): \Closure
    {
        return static function (): StartSizeAsArrayInQueryResolver {
            $queryAttr = \config('pagination.array_in_query_attr', static::$defaultQueryAttr);

            return new StartSizeAsArrayInQueryResolver(static::createConfig(), $queryAttr);
        };
    }
}
