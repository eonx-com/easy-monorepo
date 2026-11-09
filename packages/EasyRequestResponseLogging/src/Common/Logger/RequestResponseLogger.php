<?php
declare(strict_types=1);

namespace EonX\EasyRequestResponseLogging\Common\Logger;

use EonX\EasyRequestResponseLogging\Common\Builder\RequestResponseDataBuilder;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final readonly class RequestResponseLogger
{
    /**
     * @param string[] $doNotLogPaths
     */
    public function __construct(
        private LoggerInterface $logger,
        private RequestResponseDataBuilder $requestResponseDataBuilder,
        private array $doNotLogPaths,
    ) {
    }

    /**
     * @param array|null $routeParameters Null when no route matched the request
     */
    public function log(
        Request $request,
        Response $response,
        ?float $durationMs,
        ?string $route,
        ?array $routeParameters,
    ): void {
        try {
            if (\in_array($request->getPathInfo(), $this->doNotLogPaths, true)) {
                return;
            }

            $requestData = $this->requestResponseDataBuilder->buildRequestData($request, $route, $routeParameters);
            $responseData = $this->requestResponseDataBuilder->buildResponseData($response, $durationMs);

            $this->logger->info(
                \sprintf(
                    'Request: "%s %s" -> %d',
                    $requestData->getMethod(),
                    $requestData->getPath(),
                    $responseData->getStatusCode()
                ),
                [
                    'correlation_id' => $requestData->getCorrelationId(),
                    'request' => [
                        'body' => $requestData->getBody(),
                        'body_omitted' => $requestData->isBodyOmitted(),
                        'body_truncated' => $requestData->isBodyTruncated(),
                        'headers' => $requestData->getHeaders(),
                        'method' => $requestData->getMethod(),
                        'path' => $requestData->getPath(),
                        'query' => $requestData->getQuery(),
                        'route' => $requestData->getRoute(),
                        'route_params' => $requestData->getRouteParameters(),
                    ],
                    'request_id' => $requestData->getRequestId(),
                    'response' => [
                        'body' => $responseData->getBody(),
                        'body_omitted' => $responseData->isBodyOmitted(),
                        'body_truncated' => $responseData->isBodyTruncated(),
                        'duration_ms' => $responseData->getDurationMs(),
                        'headers' => $responseData->getHeaders(),
                        'status_code' => $responseData->getStatusCode(),
                    ],
                ]
            );
        } catch (Throwable) {
            // Do nothing
        }
    }
}
