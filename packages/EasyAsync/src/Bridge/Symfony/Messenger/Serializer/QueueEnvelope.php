<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Bridge\Symfony\Messenger\Serializer;

use EonX\EasyAsync\Bridge\Symfony\Messenger\Serializer\Interfaces\QueueEnvelopeInterface;

final class QueueEnvelope implements QueueEnvelopeInterface
{
    /**
     * @param mixed[] $headers
     * @param mixed[]|null $body
     */
    public function __construct(
        private string $originalBody,
        private array $headers,
        private ?array $body = null,
    ) {
    }

    /**
     * @param mixed[] $headers
     * @param mixed[]|null $body
     */
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

    /**
     * @return mixed[]
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getOriginalBody(): string
    {
        return $this->originalBody;
    }
}
