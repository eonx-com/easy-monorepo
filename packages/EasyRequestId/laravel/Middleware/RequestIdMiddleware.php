<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\Laravel\Middleware;

use Closure;
use EonX\EasyRequestId\Common\Provider\RequestIdProviderInterface;
use EonX\EasyRequestId\Common\Resolver\ResolvesFromHttpFoundationRequestTrait;
use Illuminate\Http\Request;

final class RequestIdMiddleware
{
    use ResolvesFromHttpFoundationRequestTrait;

    public function __construct(
        private RequestIdProviderInterface $requestIdProvider,
    ) {
    }

    public function handle(Request $request, Closure $next): mixed
    {
        $this->setResolver($request, $this->requestIdProvider);

        return $next($request);
    }
}
