<?php
declare(strict_types=1);

namespace EonX\EasyRequestResponseLogging\Common\ValueObject;

final readonly class RequestData
{
    public function __construct(
        private string $method,
        private string $uri,
        private array $query,
        private array $headers,
        private ?string $body,
        private bool $bodyOmitted,
        private bool $bodyTruncated,
        private string $requestId,
        private string $correlationId,
    ) {
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function getCorrelationId(): string
    {
        return $this->correlationId;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getQuery(): array
    {
        return $this->query;
    }

    public function getRequestId(): string
    {
        return $this->requestId;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function isBodyOmitted(): bool
    {
        return $this->bodyOmitted;
    }

    public function isBodyTruncated(): bool
    {
        return $this->bodyTruncated;
    }
}
