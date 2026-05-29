<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Laravel\Middleware;

use Closure;
use EonX\EasyServerless\Aws\Helper\LambdaContextHelper;
use EonX\EasyServerless\SecurityHeader\Hydrator\SecurityHeadersHydrator;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class SecurityHeadersResponseMiddleware
{
    public function __construct(
        private SecurityHeadersHydrator $securityHeadersHydrator,
    ) {
    }

    public function handle(Request $request, Closure $next): mixed
    {
        if (LambdaContextHelper::inLambda() === false) {
            return $next($request);
        }

        $response = $next($request);
        if ($response instanceof Response) {
            $response = $this->securityHeadersHydrator->hydrateResponse($response);
        }

        return $response;
    }
}
