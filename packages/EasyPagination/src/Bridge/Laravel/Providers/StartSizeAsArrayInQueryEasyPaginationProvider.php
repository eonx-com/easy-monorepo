<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Bridge\Laravel\Providers;

use EonX\EasyPagination\Resolvers\StartSizeAsArrayInQueryResolver;

final class StartSizeAsArrayInQueryEasyPaginationProvider extends AbstractStartSizeEasyPaginationProvider
{
    /**
     * @var string
     */
    private static $defaultQueryAttr = 'page';

    protected function getResolverClosure(): \Closure
    {
        return function (): StartSizeAsArrayInQueryResolver {
            $queryAttr = $this->app->make('config')
                ->get('pagination.array_in_query_attr', static::$defaultQueryAttr);

            return new StartSizeAsArrayInQueryResolver($this->createConfig(), $queryAttr);
        };
    }
}
