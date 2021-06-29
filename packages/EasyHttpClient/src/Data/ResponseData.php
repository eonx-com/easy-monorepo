<?php

declare(strict_types=1);

namespace EonX\EasyHttpClient\Data;

use EonX\EasyHttpClient\Interfaces\ResponseDataInterface;

final class ResponseData implements ResponseDataInterface
{
    /**
     * @var string
     */
    private $content;

    /**
     * @var mixed[]
     */
    private $headers;

    /**
     * @var \DateTimeInterface
     */
    private $receivedAt;

    /**
     * @var int
     */
    private $statusCode;

    /**
     * @param mixed[] $headers
     */
    public function __construct(string $content, array $headers, \DateTimeInterface $receivedAt, int $statusCode)
    {
        $this->content = $content;
        $this->headers = $headers;
        $this->receivedAt = $receivedAt;
        $this->statusCode = $statusCode;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @return mixed[]
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getReceivedAt(): \DateTimeInterface
    {
        return $this->receivedAt;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
