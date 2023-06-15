<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Laravel\Middleware;

use Illuminate\Http\Request;

final class SetContentLength
{
    /**
     * @return mixed
     */
    public function handle(Request $request, \Closure $next)
    {
        /** @var \Illuminate\Http\Request $response */
        $response = $next($request);
        $response->headers->set('Content-Length', (string)\strlen($response->getContent()));

        return $response;
    }
}
