<?php
declare(strict_types=1);

namespace EonX\EasyRequestResponseLogging\Common\Subscriber;

use Closure;
use EonX\EasyRequestId\Common\Provider\RequestIdProviderInterface;
use EonX\EasyRequestResponseLogging\Common\ValueObject\RequestData;
use EonX\EasyRequestResponseLogging\Common\ValueObject\ResponseData;
use EonX\EasyUtils\SensitiveData\Sanitizer\SensitiveDataSanitizerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Throwable;
use UnexpectedValueException;

final readonly class RequestResponseLoggingSubscriber implements EventSubscriberInterface
{
    private const CARD_NUMBER_MIN_DIGITS = 12;

    private const DURATION_MS_ATTRIBUTE = '_easy_request_response_logging_duration_ms';

    private const FORM_CONTENT_TYPE = 'application/x-www-form-urlencoded';

    private const JSON_CONTENT_TYPE = 'application/json';

    private const JSON_CONTENT_TYPE_SUFFIX = '+json';

    private const OMITTED_VALUE = '[omitted]';

    private const STARTED_AT_ATTRIBUTE = '_easy_request_response_logging_started_at';

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
     * @return array<string, array{0: string, 1: int}>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => ['onKernelRequest', 1024],
            ResponseEvent::class => ['onKernelResponse', -1024],
            TerminateEvent::class => ['onKernelTerminate', -1024],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if ($event->isMainRequest() === false) {
            return;
        }

        $event->getRequest()
            ->attributes
            ->set(self::STARTED_AT_ATTRIBUTE, \microtime(true));
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if ($event->isMainRequest() === false) {
            return;
        }

        $attributes = $event->getRequest()
            ->attributes;
        $startedAt = $attributes->get(self::STARTED_AT_ATTRIBUTE);

        if (\is_float($startedAt)) {
            $attributes->set(self::DURATION_MS_ATTRIBUTE, \round((\microtime(true) - $startedAt) * 1000, 2));
        }
    }

    public function onKernelTerminate(TerminateEvent $event): void
    {
        try {
            $request = $event->getRequest();

            if (\in_array($request->getPathInfo(), $this->doNotLogPaths, true)) {
                return;
            }

            $requestData = $this->createRequestData($request);
            $responseData = $this->createResponseData($request, $event->getResponse());

            $this->logger->info(
                \sprintf(
                    'Request: "%s %s" -> %d',
                    $requestData->getMethod(),
                    $requestData->getUri(),
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
                        'query' => $requestData->getQuery(),
                        'route' => $requestData->getRoute(),
                        'route_params' => $requestData->getRouteParameters(),
                        'uri' => $requestData->getUri(),
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

    private function createRequestData(Request $request): RequestData
    {
        [$body, $bodyOmitted, $bodyTruncated] = $this->prepareBody(
            $this->logRequestBody,
            $request->headers,
            static fn (): string => $request->getContent()
        );
        $routeParameters = $this->getRouteParameters($request);
        $sanitizedRouteParameters = $this->sanitizeEntries($routeParameters);

        return new RequestData(
            method: $request->getMethod(),
            uri: $this->sanitizePath($request, $routeParameters, $sanitizedRouteParameters),
            route: $this->getRoute($request),
            routeParameters: $sanitizedRouteParameters,
            query: $this->sanitizeEntries($this->getQueryParameters($request)),
            headers: $this->sanitizeEntries($request->headers->all()),
            body: $body,
            bodyOmitted: $bodyOmitted,
            bodyTruncated: $bodyTruncated,
            requestId: $this->sanitizeString($this->requestIdProvider->getRequestId()),
            correlationId: $this->sanitizeString($this->requestIdProvider->getCorrelationId())
        );
    }

    private function createResponseData(Request $request, Response $response): ResponseData
    {
        [$body, $bodyOmitted, $bodyTruncated] = $this->prepareBody(
            $this->logResponseBody,
            $response->headers,
            static fn (): ?string => $response->getContent() === false ? null : $response->getContent()
        );

        $durationMs = $request->attributes->get(self::DURATION_MS_ATTRIBUTE);

        return new ResponseData(
            statusCode: $response->getStatusCode(),
            headers: $this->sanitizeEntries($response->headers->all()),
            body: $body,
            bodyOmitted: $bodyOmitted,
            bodyTruncated: $bodyTruncated,
            durationMs: \is_float($durationMs) ? $durationMs : null
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

    private function getRoute(Request $request): ?string
    {
        $route = $request->attributes->get('_route');

        return \is_string($route) ? $route : null;
    }

    private function getRouteParameters(Request $request): array
    {
        $routeParameters = $request->attributes->get('_route_params');

        return \is_array($routeParameters) ? $routeParameters : [];
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

        return [\substr($body, 0, $this->maxBodySize), false, true];
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

    private function sanitizePath(Request $request, array $routeParameters, array $sanitizedRouteParameters): string
    {
        $path = \rawurldecode($request->getPathInfo());

        if ($this->getRoute($request) === null) {
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
