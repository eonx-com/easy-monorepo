<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Bridge\Laravel\Middleware;

use EonX\EasyPagination\Interfaces\PaginationProviderInterface;
use EonX\EasyPagination\Resolvers\FromHttpFoundationRequestResolver;
use Illuminate\Http\Request;

final class PaginationFromRequestMiddleware
{
    /**
     * @var \EonX\EasyPagination\Interfaces\PaginationProviderInterface
     */
    private $paginationProvider;

    public function __construct(PaginationProviderInterface $paginationProvider)
    {
        $this->paginationProvider = $paginationProvider;
    }

    /**
     * @return mixed
     */
    public function handle(Request $request, \Closure $next)
    {
        $resolver = new FromHttpFoundationRequestResolver(
            $this->paginationProvider->getPaginationConfig(),
            $request
        );

        $this->paginationProvider->setResolver($resolver);

        return $next($request);
    }
}
