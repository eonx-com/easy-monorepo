<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Bridge\Laravel\Session;

use Closure;
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
