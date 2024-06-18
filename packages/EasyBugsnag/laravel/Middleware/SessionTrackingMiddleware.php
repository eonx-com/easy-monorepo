<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Laravel\Middleware;

use Closure;
use EonX\EasyBugsnag\Laravel\Trackers\TracksSessionTrait;
use Illuminate\Http\Request;

final class SessionTrackingMiddleware
{
    use TracksSessionTrait;

    public function handle(Request $request, Closure $next): mixed
    {
        $this->trackSession($request);

        return $next($request);
    }
}
