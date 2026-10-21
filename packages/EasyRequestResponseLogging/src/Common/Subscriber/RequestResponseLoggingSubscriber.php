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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Throwable;

final readonly class RequestResponseLoggingSubscriber implements EventSubscriberInterface
{
    private const DURATION_MS_ATTRIBUTE = '_easy_request_response_logging_duration_ms';

    private const FORM_CONTENT_TYPE = 'application/x-www-form-urlencoded';

    private const JSON_CONTENT_TYPE = 'application/json';

    private const JSON_CONTENT_TYPE_SUFFIX = '+json';

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
            $request->headers->get('Content-Type'),
            static fn (): string => $request->getContent()
        );

        return new RequestData(
            method: $request->getMethod(),
            uri: $this->sanitizePath($request->getPathInfo()),
            query: $this->sensitiveDataSanitizer->sanitize($request->query->all()),
            headers: $this->sensitiveDataSanitizer->sanitize($request->headers->all()),
            body: $body,
            bodyOmitted: $bodyOmitted,
            bodyTruncated: $bodyTruncated,
            requestId: $this->requestIdProvider->getRequestId(),
            correlationId: $this->requestIdProvider->getCorrelationId()
        );
    }

    private function createResponseData(Request $request, Response $response): ResponseData
    {
        [$body, $bodyOmitted, $bodyTruncated] = $this->prepareBody(
            $this->logResponseBody,
            $response->headers->get('Content-Type'),
            static fn (): ?string => $response->getContent() === false ? null : $response->getContent()
        );

        $durationMs = $request->attributes->get(self::DURATION_MS_ATTRIBUTE);

        return new ResponseData(
            statusCode: $response->getStatusCode(),
            headers: $this->sensitiveDataSanitizer->sanitize($response->headers->all()),
            body: $body,
            bodyOmitted: $bodyOmitted,
            bodyTruncated: $bodyTruncated,
            durationMs: \is_float($durationMs) ? $durationMs : null
        );
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
    private function prepareBody(bool $logBody, ?string $contentType, Closure $bodyResolver): array
    {
        $mimeType = \strtolower(\trim(\explode(';', $contentType ?? '')[0]));

        if ($logBody === false || $this->isLoggableMimeType($mimeType) === false) {
            return [null, false, false];
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
            return $this->sensitiveDataSanitizer->sanitize($body);
        }

        // Nested fields are percent-encoded on the wire (e.g. "user%5Bpassword%5D"), so the fields are
        // sanitized once parsed and decoded back for readability
        \parse_str($body, $fields);

        return \urldecode(\http_build_query($this->sensitiveDataSanitizer->sanitize($fields)));
    }

    private function sanitizePath(string $path): string
    {
        // The path info keeps its percent-encoding, so a sensitive segment name could be encoded (e.g. "/%74oken/").
        // Only unreserved characters are decoded (RFC 3986 normalization): encoded delimiters such as "%2F" or "%26"
        // stay encoded and cannot split a sensitive value
        $normalizedPath = (string)\preg_replace_callback(
            '/%([0-9A-Fa-f]{2})/',
            static function (array $matches): string {
                $char = \chr((int)\hexdec($matches[1]));

                return \preg_match('/^[A-Za-z0-9._~-]$/', $char) === 1 ? $char : \strtoupper($matches[0]);
            },
            $path
        );

        return $this->sensitiveDataSanitizer->sanitize($normalizedPath);
    }
}
