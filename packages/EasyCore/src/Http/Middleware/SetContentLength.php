<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyCore\Http\Middleware;

use Illuminate\Http\Request;

final class SetContentLength
{
    /**
     * Add content-length header to the response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, \Closure $next)
    {
        $response = $next($request);
        $response->headers->set('Content-Length', \strlen($response->getContent()));

        return $response;
    }
}
