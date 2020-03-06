<?php
declare(strict_types=1);

namespace EonX\EasyCore\Http\Middleware;

use Illuminate\Http\Request;

final class SetContentLength
{
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
