<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Stub\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class ShortCircuitMiddlewareStub
{
    public const string RESPONSE_CONTENT = 'short-circuit';

    public function handle(Request $request, Closure $next): Response
    {
        return new Response(self::RESPONSE_CONTENT);
    }
}
