<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Bridge\Laravel\Providers;

use EonX\EasyPagination\Resolvers\StartSizeAsArrayInQueryResolver;
use Illuminate\Config\Repository;

final class StartSizeAsArrayInQueryEasyPaginationProvider extends AbstractStartSizeEasyPaginationProvider
{
    /**
     * @var string
     */
    private static $defaultQueryAttr = 'page';

    protected function getResolverClosure(): \Closure
    {
        return function (): StartSizeAsArrayInQueryResolver {
            /** @var Repository $configRepository */
            $configRepository = $this->app->make('config');
            $queryAttr = $configRepository->get('pagination.array_in_query_attr', static::$defaultQueryAttr);

            return new StartSizeAsArrayInQueryResolver($this->createConfig(), $queryAttr);
        };
    }
}
