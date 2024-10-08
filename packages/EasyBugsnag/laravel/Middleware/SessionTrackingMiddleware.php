<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Laravel\Middleware;

use Closure;
use EonX\EasyBugsnag\Laravel\Trackers\SessionTrackerTrait;
use Illuminate\Http\Request;

final class SessionTrackingMiddleware
{
    use SessionTrackerTrait;

    public function handle(Request $request, Closure $next): mixed
    {
        $this->trackSession($request);

        return $next($request);
    }
}
