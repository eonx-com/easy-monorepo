<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Laravel\Middleware;

use Closure;
use EonX\EasyPagination\Provider\PaginationProviderInterface;
use EonX\EasyPagination\Resolver\FromHttpFoundationRequestPaginationResolver;
use Illuminate\Http\Request;

final class PaginationFromRequestMiddleware
{
    public function __construct(
        private PaginationProviderInterface $paginationProvider,
    ) {
    }

    public function handle(Request $request, Closure $next): mixed
    {
        $resolver = new FromHttpFoundationRequestPaginationResolver(
            $this->paginationProvider->getPaginationConfig(),
            $request
        );

        $this->paginationProvider->setResolver($resolver);

        return $next($request);
    }
}
