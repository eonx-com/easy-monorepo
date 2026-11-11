<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Laravel\Middleware;

use Closure;
use EonX\EasyServerless\Datadog\Helper\RootSpanHelper;
use Illuminate\Http\Request;

/**
 * Registered as the first global middleware, so unmatched routes and requests short-circuited by other global
 * middleware (e.g. maintenance mode) are covered too.
 */
final class PhpRuntimeInfoMiddleware
{
    public function handle(Request $request, Closure $next): mixed
    {
        RootSpanHelper::addPhpRuntimeInfo();

        return $next($request);
    }
}
