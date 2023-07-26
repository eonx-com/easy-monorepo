<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Bridge\Laravel\Middleware;

use Closure;
use EonX\EasyPagination\Interfaces\PaginationProviderInterface;
use EonX\EasyPagination\Resolvers\FromHttpFoundationRequestResolver;
use Illuminate\Http\Request;

final class PaginationFromRequestMiddleware
{
    public function __construct(
        private PaginationProviderInterface $paginationProvider,
    ) {
    }

    public function handle(Request $request, Closure $next): mixed
    {
        $resolver = new FromHttpFoundationRequestResolver(
            $this->paginationProvider->getPaginationConfig(),
            $request
        );

        $this->paginationProvider->setResolver($resolver);

        return $next($request);
    }
}
