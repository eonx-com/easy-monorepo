<?php
declare(strict_types=1);

namespace EonX\EasyHttpClient\Common\ValueObject;

use DateTimeInterface;

final class ResponseData
{
    public function __construct(
        private string $content,
        private readonly array $headers,
        private readonly DateTimeInterface $receivedAt,
        private readonly int $statusCode,
    ) {
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getReceivedAt(): DateTimeInterface
    {
        return $this->receivedAt;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }
}
