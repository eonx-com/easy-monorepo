<?php
declare(strict_types=1);

namespace EonX\EasyRequestResponseLogging\Common\ValueObject;

final readonly class ResponseData
{
    public function __construct(
        private int $statusCode,
        private array $headers,
        private ?string $body,
        private bool $bodyTruncated,
        private float $durationMs,
    ) {
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function getDurationMs(): float
    {
        return $this->durationMs;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function isBodyTruncated(): bool
    {
        return $this->bodyTruncated;
    }
}
