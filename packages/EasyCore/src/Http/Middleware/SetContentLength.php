<?php

declare(strict_types=1);

namespace EonX\EasyCore\Http\Middleware;

use Illuminate\Http\Request;

final class SetContentLength
{
    public function __construct()
    {
        @\trigger_error(\sprintf(
            '%s is deprecated since 2.3.1 and will be removed in 3.0, use %s instead',
            static::class,
            \EonX\EasyCore\Bridge\Laravel\Middleware\SetContentLength::class
        ), \E_USER_DEPRECATED);
    }

    /**
     * @return mixed
     */
    public function handle(Request $request, \Closure $next)
    {
        $response = $next($request);
        $response->headers->set('Content-Length', \strlen($response->getContent()));

        return $response;
    }
}
