<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\ValueObject;

use EonX\EasyUtils\Common\Enum\HttpStatusCode;

final class ErrorResponseData implements ErrorResponseDataInterface
{
    private readonly array $headers;

    private readonly HttpStatusCode $statusCode;

    public function __construct(
        private readonly array $rawData,
        ?HttpStatusCode $statusCode = null,
        ?array $headers = null,
    ) {
        $this->statusCode = $statusCode ?? HttpStatusCode::InternalServerError;
        $this->headers = $headers ?? [];
    }

    public static function create(array $rawData, ?HttpStatusCode $statusCode = null, ?array $headers = null): self
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

    public function getStatusCode(): HttpStatusCode
    {
        return $this->statusCode;
    }
}
