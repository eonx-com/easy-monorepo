<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Laravel\Middleware;

use Closure;
use EonX\EasyServerless\Datadog\Helper\RootSpanHelper;
use Illuminate\Http\Request;

/**
 * Global middleware so every request is covered, including unmatched routes (404/405) and requests
 * short-circuited by other global middleware such as maintenance mode, which never dispatch RouteMatched.
 */
final class PhpRuntimeInfoMiddleware
{
    public function handle(Request $request, Closure $next): mixed
    {
        RootSpanHelper::addPhpRuntimeInfo();

        return $next($request);
    }
}
