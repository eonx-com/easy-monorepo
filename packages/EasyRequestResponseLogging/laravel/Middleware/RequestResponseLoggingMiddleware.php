<?php
declare(strict_types=1);

namespace EonX\EasyRequestResponseLogging\Laravel\Middleware;

use Closure;
use EonX\EasyRequestResponseLogging\Common\Logger\RequestResponseLogger;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Symfony\Component\HttpFoundation\Response;

final readonly class RequestResponseLoggingMiddleware
{
    private const string DURATION_MS_ATTRIBUTE = '_easy_request_response_logging_duration_ms';

    public function __construct(
        private RequestResponseLogger $requestResponseLogger,
    ) {}

    public function handle(Request $request, Closure $next): mixed
    {
        try {
            return $next($request);
        } finally {
            // Set per request by PHP-FPM, Bref and OpenSwoole, so it also covers the application boot
            $startedAt = $request->server->get('REQUEST_TIME_FLOAT');

            if (\is_float($startedAt)) {
                $request->attributes->set(
                    self::DURATION_MS_ATTRIBUTE,
                    \round((\microtime(true) - $startedAt) * 1000, 2)
                );
            }
        }
    }

    public function terminate(Request $request, Response $response): void
    {
        $durationMs = $request->attributes->get(self::DURATION_MS_ATTRIBUTE);
        [$route, $routeParameters] = $this->resolveRoute($request);

        $this->requestResponseLogger->log(
            request: $request,
            response: $response,
            durationMs: \is_float($durationMs) ? $durationMs : null,
            route: $route,
            routeParameters: $routeParameters
        );
    }

    /**
     * @return array{0: string|null, 1: array|null}
     */
    private function resolveRoute(Request $request): array
    {
        $route = $request->route();

        // Original parameters are the raw path values, before route model binding
        if ($route instanceof Route) {
            return [$route->getName(), $route->originalParameters()];
        }

        return [null, null];
    }
}
