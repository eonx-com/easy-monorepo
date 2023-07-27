<?php

declare(strict_types=1);

namespace EonX\EasyHttpClient\Data;

use DateTimeInterface;
use EonX\EasyHttpClient\Interfaces\ResponseDataInterface;

final class ResponseData implements ResponseDataInterface
{
    public function __construct(
        private string $content,
        private array $headers,
        private DateTimeInterface $receivedAt,
        private int $statusCode,
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
}
