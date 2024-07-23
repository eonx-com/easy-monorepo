<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Messenger\Envelope;

final class QueueEnvelope implements QueueEnvelopeInterface
{
    public function __construct(
        private readonly string $originalBody,
        private array $headers,
        private readonly ?array $body = null,
    ) {
    }

    public static function create(string $originalBody, array $headers, ?array $body = null): QueueEnvelopeInterface
    {
        return new self($originalBody, $headers, $body);
    }

    public function getBody(): ?array
    {
        return $this->body;
    }

    public function getHeader(string $header, mixed $default = null): mixed
    {
        return $this->headers[$header] ?? $default;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getOriginalBody(): string
    {
        return $this->originalBody;
    }
}
