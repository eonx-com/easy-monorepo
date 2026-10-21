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
        // PHP alters some parameter names (e.g. "payment.secret" becomes "payment_secret"), which would then no longer
        // match the configured keys, so the raw query string is parsed again with the names preserved
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

        // A body declared bigger than the limit is omitted without being read into memory
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
            // The sanitizer falls back to free-text sanitization for invalid JSON, which does not mask keys, so an
            // invalid JSON body must throw and be omitted instead
            $decodedBody = \json_decode(
                json: $body,
                associative: true,
                flags: \JSON_BIGINT_AS_STRING | \JSON_THROW_ON_ERROR
            );

            // The body is sanitized decoded: escape sequences of a top-level JSON string (e.g. "\u0034") would hide
            // a sensitive value from the string sanitizers, and numbers are not checked by the sanitizer at all
            if (\is_array($decodedBody) || \is_string($decodedBody)) {
                return \json_encode(
                    $this->sensitiveDataSanitizer->sanitize(
                        \is_array($decodedBody) ? $this->sanitizeNumbers($decodedBody) : $decodedBody
                    ),
                    \JSON_THROW_ON_ERROR | \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE
                );
            }

            // A top-level number is checked like a nested one and kept as is when nothing is masked
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

        // Nested fields are percent-encoded on the wire (e.g. "user%5Bpassword%5D"), so the fields are
        // sanitized once parsed and decoded back for readability. Dots in field names are preserved, so a configured
        // key like "payment.secret" still matches
        $fields = HeaderUtils::parseQuery($body);

        // A top-level field name altered when parsed (e.g. a leading space is trimmed) would no longer match the
        // configured keys, so such a body is omitted
        foreach (\explode('&', $body) as $pair) {
            $fieldName = \explode('[', \urldecode(\explode('=', $pair, 2)[0]), 2)[0];

            if ($fieldName !== '' && \array_key_exists($fieldName, $fields) === false) {
                throw new UnexpectedValueException('Form field names cannot be preserved.');
            }
        }

        return \urldecode(\http_build_query($this->sensitiveDataSanitizer->sanitize($fields)));
    }

    /**
     * Each entry is sanitized on its own, so an entry the sanitizer fails on (e.g. a JSON value with an infinite
     * number) is logged as null instead of failing the whole record or being logged unsanitized.
     */
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

            // A card number sent as a JSON number, e.g. {"note":4111111111111111} or {"note":4.111111111111111e15},
            // is masked like its string form. Floats are written without exponent, other numbers are kept as is
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
        // The path is logged decoded, as the router and the application see it
        $path = \rawurldecode($request->getPathInfo());

        // Without a matched route the path has no known structure, so only the sanitizer itself applies to it
        if ($this->getRoute($request) === null) {
            return $this->sanitizeString($path) ?? self::OMITTED_VALUE;
        }

        // The router has already split the path into named parameters, so each parameter value the sanitizer masked
        // (by its name or by its value) is replaced in the path, however it was encoded in the request
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
