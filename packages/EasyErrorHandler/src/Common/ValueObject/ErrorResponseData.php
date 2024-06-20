<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\ValueObject;

use Symfony\Component\HttpFoundation\Response;

final class ErrorResponseData implements ErrorResponseDataInterface
{
    private readonly array $headers;

    private readonly int $statusCode;

    public function __construct(
        private readonly array $rawData,
        ?int $statusCode = null,
        ?array $headers = null,
    ) {
        $this->statusCode = $statusCode ?? Response::HTTP_INTERNAL_SERVER_ERROR;
        $this->headers = $headers ?? [];
    }

    public static function create(array $rawData, ?int $statusCode = null, ?array $headers = null): self
    {
        return new self($rawData, $statusCode, $headers);
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getRawData(): array
    {
        return $this->rawData;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
