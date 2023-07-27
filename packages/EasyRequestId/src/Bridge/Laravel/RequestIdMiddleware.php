<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\Bridge\Laravel;

use Closure;
use EonX\EasyRequestId\Interfaces\RequestIdServiceInterface;
use EonX\EasyRequestId\Traits\ResolvesFromHttpFoundationRequest;
use Illuminate\Http\Request;

final class RequestIdMiddleware
{
    use ResolvesFromHttpFoundationRequest;

    public function __construct(
        private RequestIdServiceInterface $requestIdService,
    ) {
    }

    public function handle(Request $request, Closure $next): mixed
    {
        $this->setResolver($request, $this->requestIdService);

        return $next($request);
    }
}
