<?php
declare(strict_types=1);

namespace EonX\EasyRequestResponseLogging\Common\Listener;

use Closure;
use EonX\EasyRequestId\Common\Provider\RequestIdProviderInterface;
use EonX\EasyRequestResponseLogging\Common\ValueObject\RequestData;
use EonX\EasyRequestResponseLogging\Common\ValueObject\ResponseData;
use EonX\EasyUtils\SensitiveData\Sanitizer\SensitiveDataSanitizerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Throwable;

final readonly class RequestResponseLoggingListener
{
    private const DURATION_MS_ATTRIBUTE = '_easy_request_response_logging_duration_ms';

    private const LOGGABLE_CONTENT_TYPES = [
        'application/json',
        'application/x-www-form-urlencoded',
        'application/xml',
        'text/plain',
        'text/xml',
    ];

    private const LOGGABLE_CONTENT_TYPE_SUFFIXES = ['+json', '+xml'];

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
                    'body_truncated' => $requestData->isBodyTruncated(),
                    'headers' => $requestData->getHeaders(),
                    'method' => $requestData->getMethod(),
                    'query' => $requestData->getQuery(),
                    'uri' => $requestData->getUri(),
                ],
                'request_id' => $requestData->getRequestId(),
                'response' => [
                    'body' => $responseData->getBody(),
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
        [$body, $bodyTruncated] = $this->prepareBody(
            $this->logRequestBody,
            $request->headers->get('Content-Type'),
            static fn (): string => $request->getContent()
        );

        return new RequestData(
            method: $request->getMethod(),
            uri: $request->getPathInfo(),
            query: $this->sensitiveDataSanitizer->sanitize($request->query->all()),
            headers: $this->sensitiveDataSanitizer->sanitize($request->headers->all()),
            body: $body,
            bodyTruncated: $bodyTruncated,
            requestId: $this->requestIdProvider->getRequestId(),
            correlationId: $this->requestIdProvider->getCorrelationId()
        );
    }

    private function createResponseData(Request $request, Response $response): ResponseData
    {
        [$body, $bodyTruncated] = $this->prepareBody(
            $this->logResponseBody,
            $response->headers->get('Content-Type'),
            static fn (): ?string => $response->getContent() === false ? null : $response->getContent()
        );

        $durationMs = $request->attributes->get(self::DURATION_MS_ATTRIBUTE);

        return new ResponseData(
            statusCode: $response->getStatusCode(),
            headers: $this->sensitiveDataSanitizer->sanitize($response->headers->all()),
            body: $body,
            bodyTruncated: $bodyTruncated,
            durationMs: \is_float($durationMs) ? $durationMs : 0.0
        );
    }

    private function isLoggableContentType(?string $contentType): bool
    {
        if ($contentType === null) {
            return false;
        }

        $mimeType = \strtolower(\trim(\explode(';', $contentType)[0]));

        if (\in_array($mimeType, self::LOGGABLE_CONTENT_TYPES, true)) {
            return true;
        }

        foreach (self::LOGGABLE_CONTENT_TYPE_SUFFIXES as $suffix) {
            if (\str_ends_with($mimeType, $suffix)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array{0: string|null, 1: bool}
     */
    private function prepareBody(bool $logBody, ?string $contentType, Closure $bodyResolver): array
    {
        if ($logBody === false || $this->isLoggableContentType($contentType) === false) {
            return [null, false];
        }

        /** @var string|null $body */
        $body = $bodyResolver();

        if ($body !== null && \strlen($body) > $this->maxRawBodySize) {
            return [null, true];
        }

        if ($body === null || $body === '') {
            return [$body, false];
        }

        try {
            $body = $this->sensitiveDataSanitizer->sanitize($body);
        } catch (Throwable) {
            return [null, true];
        }

        if (\strlen($body) <= $this->maxBodySize) {
            return [$body, false];
        }

        return [\substr($body, 0, $this->maxBodySize), true];
    }
}
