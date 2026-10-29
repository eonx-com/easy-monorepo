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
    private const DURATION_MS_ATTRIBUTE = '_easy_request_response_logging_duration_ms';

    public function __construct(
        private RequestResponseLogger $requestResponseLogger,
    ) {
    }

    public function handle(Request $request, Closure $next): mixed
    {
        $startedAt = \microtime(true);

        try {
            return $next($request);
        } finally {
            // Set also when an exception is thrown, as Lumen renders it outside the middleware pipeline
            $request->attributes->set(
                self::DURATION_MS_ATTRIBUTE,
                \round((\microtime(true) - $startedAt) * 1000, 2)
            );
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
        /** @var \Illuminate\Routing\Route|array|object|string|null $route Lumen request returns an array */
        $route = $request->route();

        // Laravel: original parameters are the raw path values, before route model binding
        if ($route instanceof Route) {
            return [$route->getName(), $route->originalParameters()];
        }

        // Lumen: [status, action, parameters]
        if (\is_array($route)) {
            $name = $route[1]['as'] ?? null;
            $parameters = \is_array($route[2] ?? null) ? $route[2] : [];

            // Unlike Laravel, Lumen keeps parameters percent-encoded, while the logged path is decoded
            foreach ($parameters as $key => $value) {
                if (\is_string($value)) {
                    $parameters[$key] = \rawurldecode($value);
                }
            }

            return [\is_string($name) ? $name : null, $parameters];
        }

        return [null, null];
    }
}
