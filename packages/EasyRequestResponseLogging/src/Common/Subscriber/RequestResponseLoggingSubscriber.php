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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Throwable;

final readonly class RequestResponseLoggingSubscriber implements EventSubscriberInterface
{
    private const CARD_NUMBER_MIN_DIGITS = 12;

    private const DURATION_MS_ATTRIBUTE = '_easy_request_response_logging_duration_ms';

    private const FORM_CONTENT_TYPE = 'application/x-www-form-urlencoded';

    private const JSON_CONTENT_TYPE = 'application/json';

    private const JSON_CONTENT_TYPE_SUFFIX = '+json';

    private const OMITTED_PATH_SEGMENT = '[omitted]';

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

        return new RequestData(
            method: $request->getMethod(),
            uri: $this->sanitizePath($request->getPathInfo()),
            query: $this->sanitizeEntries($request->query->all()),
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

    private function getKeyMask(string $key): ?string
    {
        // The sanitizer is asked directly, so the path is masked with its own keys and mask pattern
        $sanitized = $this->sensitiveDataSanitizer->sanitize([$key => true]);
        $value = $sanitized[$key] ?? true;

        return \is_string($value) ? $value : null;
    }

    private function isLoggableMimeType(string $mimeType): bool
    {
        return $mimeType === self::FORM_CONTENT_TYPE
            || $mimeType === self::JSON_CONTENT_TYPE
            || \str_ends_with($mimeType, self::JSON_CONTENT_TYPE_SUFFIX);
    }

    private function normalizePathSegment(string $segment): string
    {
        // Only unreserved characters are decoded (RFC 3986 normalization), e.g. "%74oken" becomes "token"
        return (string)\preg_replace_callback(
            '/%([0-9A-Fa-f]{2})/',
            static function (array $matches): string {
                $char = \chr((int)\hexdec($matches[1]));

                return \preg_match('/^[A-Za-z0-9._~-]$/', $char) === 1 ? $char : \strtoupper($matches[0]);
            },
            $segment
        );
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
            // a sensitive value from the string sanitizers, and integers are not checked by the sanitizer at all
            if (\is_array($decodedBody) || \is_string($decodedBody)) {
                return \json_encode(
                    $this->sensitiveDataSanitizer->sanitize(
                        \is_array($decodedBody) ? $this->sanitizeIntegers($decodedBody) : $decodedBody
                    ),
                    \JSON_THROW_ON_ERROR | \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE
                );
            }

            return $this->sensitiveDataSanitizer->sanitize($body);
        }

        // Nested fields are percent-encoded on the wire (e.g. "user%5Bpassword%5D"), so the fields are
        // sanitized once parsed and decoded back for readability
        \parse_str($body, $fields);

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

    private function sanitizeIntegers(array $data): array
    {
        foreach ($data as $key => $value) {
            if (\is_array($value)) {
                $data[$key] = $this->sanitizeIntegers($value);

                continue;
            }

            // A card number sent as a JSON number, e.g. {"note":4111111111111111}, is masked like its string form
            if (\is_int($value) && \strlen((string)\abs($value)) >= self::CARD_NUMBER_MIN_DIGITS) {
                $sanitizedValue = $this->sensitiveDataSanitizer->sanitize((string)$value);

                if ($sanitizedValue !== (string)$value) {
                    $data[$key] = $sanitizedValue;
                }
            }
        }

        return $data;
    }

    private function sanitizePath(string $path): string
    {
        // The router decodes the whole path (e.g. "%2F" is a separator for it) while the path info keeps its
        // percent-encoding. Each raw segment is decoded on its own and a sensitive value is masked up to the end of
        // its raw segment, so neither an encoded key nor an encoded delimiter can leave part of the value unmasked
        $segments = \explode('/', $path);
        $nextSegmentMask = null;

        foreach ($segments as $index => $segment) {
            $decodedSegment = \rawurldecode($segment);
            $segmentMask = $nextSegmentMask;

            // A key ending the segment, e.g. "/token/<value>", masks the whole next segment. It is checked for every
            // segment, including masked ones, so a key hidden behind an encoded separator is never lost
            $nextSegmentMask = $this->getKeyMask((string)\preg_replace('/^.*[\/=&;,]/s', '', $decodedSegment));

            if ($segmentMask !== null) {
                $segments[$index] = $segmentMask;

                continue;
            }

            $segments[$index] = $this->normalizePathSegment($segment);

            // A key followed by "/" or "=" inside the segment, e.g. "token=<value>" or "token%2F<value>"
            \preg_match_all('/([^\/=&;,]*)[\/=]/', $decodedSegment, $matches, \PREG_SET_ORDER | \PREG_OFFSET_CAPTURE);

            foreach ($matches as $match) {
                $mask = $this->getKeyMask($match[1][0]);

                if ($mask !== null) {
                    $segments[$index] = $this->sanitizeString(
                        \substr($decodedSegment, 0, $match[0][1] + \strlen($match[0][0])) . $mask
                    ) ?? self::OMITTED_PATH_SEGMENT;

                    continue 2;
                }
            }

            // Value-based sanitizers (e.g. card numbers) check the decoded segment, so an encoded grouping character
            // (e.g. "%20") cannot hide a value. The segment is kept normalized when nothing is masked
            $sanitizedSegment = $this->sanitizeString($decodedSegment);

            if ($sanitizedSegment !== $decodedSegment) {
                $segments[$index] = $sanitizedSegment ?? self::OMITTED_PATH_SEGMENT;
            }
        }

        return \implode('/', $segments);
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
