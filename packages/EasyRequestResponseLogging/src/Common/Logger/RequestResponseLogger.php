<?php
declare(strict_types=1);

namespace EonX\EasyRequestResponseLogging\Common\Logger;

use Closure;
use EonX\EasyRequestId\Common\Provider\RequestIdProviderInterface;
use EonX\EasyRequestResponseLogging\Common\ValueObject\RequestData;
use EonX\EasyRequestResponseLogging\Common\ValueObject\ResponseData;
use EonX\EasyUtils\SensitiveData\Sanitizer\SensitiveDataSanitizerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
use UnexpectedValueException;

final readonly class RequestResponseLogger
{
    private const CARD_NUMBER_MIN_DIGITS = 12;

    private const FORM_CONTENT_TYPE = 'application/x-www-form-urlencoded';

    private const JSON_CONTENT_TYPE = 'application/json';

    private const JSON_CONTENT_TYPE_SUFFIX = '+json';

    private const OMITTED_VALUE = '[omitted]';

    /**
     * @param string[] $doNotLogPaths
     */
    public function __construct(
        private LoggerInterface $logger,
        private SensitiveDataSanitizerInterface $sensitiveDataSanitizer,
        private RequestIdProviderInterface $requestIdProvider,
        private array $doNotLogPaths,
        private bool $logRequestBody,
        private bool $logResponseBody,
        private int $maxBodySize,
        private int $maxRawBodySize,
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

            $requestData = $this->createRequestData($request, $route, $routeParameters);
            $responseData = $this->createResponseData($response, $durationMs);

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

    private function createRequestData(Request $request, ?string $route, ?array $routeParameters): RequestData
    {
        [$body, $bodyOmitted, $bodyTruncated] = $this->prepareBody(
            $this->logRequestBody,
            $request->headers,
            static fn (): string => $request->getContent()
        );
        $sanitizedRouteParameters = $routeParameters === null ? null : $this->sanitizeEntries($routeParameters);

        return new RequestData(
            method: $request->getMethod(),
            path: $this->sanitizePath($request, $routeParameters, $sanitizedRouteParameters),
            route: $route,
            routeParameters: $sanitizedRouteParameters ?? [],
            query: $this->sanitizeEntries($this->getQueryParameters($request)),
            headers: $this->sanitizeEntries($request->headers->all()),
            body: $body,
            bodyOmitted: $bodyOmitted,
            bodyTruncated: $bodyTruncated,
            requestId: $this->sanitizeString($this->requestIdProvider->getRequestId()),
            correlationId: $this->sanitizeString($this->requestIdProvider->getCorrelationId())
        );
    }

    private function createResponseData(Response $response, ?float $durationMs): ResponseData
    {
        [$body, $bodyOmitted, $bodyTruncated] = $this->prepareBody(
            $this->logResponseBody,
            $response->headers,
            static fn (): ?string => $response->getContent() === false ? null : $response->getContent()
        );

        return new ResponseData(
            statusCode: $response->getStatusCode(),
            headers: $this->sanitizeEntries($response->headers->all()),
            body: $body,
            bodyOmitted: $bodyOmitted,
            bodyTruncated: $bodyTruncated,
            durationMs: $durationMs
        );
    }

    private function getQueryParameters(Request $request): array
    {
        // PHP turns "payment.secret" into "payment_secret" in query->all(), so configured dotted keys would not match
        $queryString = $request->server->get('QUERY_STRING');

        return \is_string($queryString) && $queryString !== ''
            ? HeaderUtils::parseQuery($queryString)
            : $request->query->all();
    }

    private function isLoggableMimeType(string $mimeType): bool
    {
        return $mimeType === self::FORM_CONTENT_TYPE
            || $mimeType === self::JSON_CONTENT_TYPE
            || \str_ends_with($mimeType, self::JSON_CONTENT_TYPE_SUFFIX);
    }

    /**
     * @return array{0: string|null, 1: bool, 2: bool}
     */
    private function prepareBody(bool $logBody, HeaderBag $headers, Closure $bodyResolver): array
    {
        $mimeType = \strtolower(\trim(\explode(';', $headers->get('Content-Type') ?? '')[0]));

        if ($logBody === false || $this->isLoggableMimeType($mimeType) === false) {
            return [null, false, false];
        }

        $contentLength = $headers->get('Content-Length');

        if (\is_numeric($contentLength) && (int)$contentLength > $this->maxRawBodySize) {
            return [null, true, false];
        }

        /** @var string|null $body */
        $body = $bodyResolver();

        if ($body !== null && \strlen($body) > $this->maxRawBodySize) {
            return [null, true, false];
        }

        if ($body === null || $body === '') {
            return [$body, false, false];
        }

        try {
            $body = $this->sanitizeBody($body, $mimeType);
        } catch (Throwable) {
            return [null, true, false];
        }

        if (\strlen($body) <= $this->maxBodySize) {
            return [$body, false, false];
        }

        return [\mb_strcut($body, 0, $this->maxBodySize, 'UTF-8'), false, true];
    }

    private function sanitizeBody(string $body, string $mimeType): string
    {
        if ($mimeType !== self::FORM_CONTENT_TYPE) {
            // Invalid JSON must throw: the sanitizer would treat it as free text and not mask its keys
            $decodedBody = \json_decode(
                json: $body,
                associative: true,
                flags: \JSON_BIGINT_AS_STRING | \JSON_THROW_ON_ERROR
            );

            // Sanitized decoded: the sanitizer ignores numbers and does not decode a top-level JSON string
            if (\is_array($decodedBody) || \is_string($decodedBody)) {
                return \json_encode(
                    $this->sensitiveDataSanitizer->sanitize(
                        \is_array($decodedBody) ? $this->sanitizeNumbers($decodedBody) : $decodedBody
                    ),
                    \JSON_THROW_ON_ERROR | \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE
                );
            }

            if (\is_int($decodedBody) || \is_float($decodedBody)) {
                $sanitizedNumber = $this->sanitizeNumbers([$decodedBody])[0];

                if ($sanitizedNumber !== $decodedBody) {
                    return \json_encode(
                        $sanitizedNumber,
                        \JSON_THROW_ON_ERROR | \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE
                    );
                }
            }

            return $this->sensitiveDataSanitizer->sanitize($body);
        }

        // Unlike parse_str(), parseQuery() keeps dots in field names, so configured dotted keys still match
        $fields = HeaderUtils::parseQuery($body);

        // A field name still altered by parsing (e.g. a trimmed leading space) could bypass the configured keys
        foreach (\explode('&', $body) as $pair) {
            $fieldName = \explode('[', \urldecode(\explode('=', $pair, 2)[0]), 2)[0];

            if ($fieldName !== '' && \array_key_exists($fieldName, $fields) === false) {
                throw new UnexpectedValueException('Form field names cannot be preserved.');
            }
        }

        return \urldecode(\http_build_query($this->sensitiveDataSanitizer->sanitize($fields)));
    }

    private function sanitizeEntries(array $entries): array
    {
        foreach ($entries as $key => $value) {
            try {
                $entries[$key] = $this->sensitiveDataSanitizer->sanitize([$key => $value])[$key];
            } catch (Throwable) {
                $entries[$key] = null;
            }
        }

        return $entries;
    }

    private function sanitizeNumbers(array $data): array
    {
        foreach ($data as $key => $value) {
            if (\is_array($value)) {
                $data[$key] = $this->sanitizeNumbers($value);

                continue;
            }

            $digits = match (true) {
                \is_int($value) => (string)$value,
                \is_float($value) && \is_finite($value) && \floor($value) === $value => \sprintf('%.0f', $value),
                default => null,
            };

            if ($digits !== null && \strlen(\ltrim($digits, '-')) >= self::CARD_NUMBER_MIN_DIGITS) {
                $sanitizedValue = $this->sensitiveDataSanitizer->sanitize($digits);

                if ($sanitizedValue !== $digits) {
                    $data[$key] = $sanitizedValue;
                }
            }
        }

        return $data;
    }

    private function sanitizePath(
        Request $request,
        ?array $routeParameters,
        ?array $sanitizedRouteParameters,
    ): string {
        $path = \rawurldecode($request->getPathInfo());

        if ($routeParameters === null) {
            return $this->sanitizeString($path) ?? self::OMITTED_VALUE;
        }

        $replacements = [];

        foreach ($routeParameters as $name => $value) {
            $sanitizedValue = $sanitizedRouteParameters[$name] ?? null;

            if (\is_string($value) && $value !== '' && $sanitizedValue !== $value) {
                $replacements[$value] = \is_string($sanitizedValue) ? $sanitizedValue : self::OMITTED_VALUE;
            }
        }

        // The longest values are replaced first, so a value containing another one is masked as a whole
        return \strtr($path, $replacements);
    }

    private function sanitizeString(string $value): ?string
    {
        try {
            return $this->sensitiveDataSanitizer->sanitize($value);
        } catch (Throwable) {
            return null;
        }
    }
}
