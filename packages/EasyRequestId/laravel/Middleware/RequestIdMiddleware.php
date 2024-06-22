<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\Laravel\Middleware;

use Closure;
use EonX\EasyRequestId\Common\RequestId\RequestIdInterface;
use EonX\EasyRequestId\Common\Resolver\ResolvesFromHttpFoundationRequestTrait;
use Illuminate\Http\Request;

final class RequestIdMiddleware
{
    use ResolvesFromHttpFoundationRequestTrait;

    public function __construct(
        private RequestIdInterface $idService,
    ) {
    }

    public function handle(Request $request, Closure $next): mixed
    {
        $this->setResolver($request, $this->idService);

        return $next($request);
    }
}
