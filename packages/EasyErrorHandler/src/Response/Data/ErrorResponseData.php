<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Response\Data;

use EonX\EasyErrorHandler\Interfaces\ErrorResponseDataInterface;

final class ErrorResponseData implements ErrorResponseDataInterface
{
    /**
     * @param mixed[] $rawData
     * @param mixed[] $headers
     */
    public function __construct(
        private readonly array $rawData,
        private readonly int $statusCode = 500,
        private readonly array $headers = []
    ) {
    }

    /**
     * @param mixed[] $rawData
     * @param null|mixed[] $headers
     */
    public static function create(array $rawData, ?int $statusCode = null, ?array $headers = null): self
    {
        return new self($rawData, $statusCode ?? 500, $headers ?? []);
    }

    /**
     * @return mixed[]
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @return mixed[]
     */
    public function getRawData(): array
    {
        return $this->rawData;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
