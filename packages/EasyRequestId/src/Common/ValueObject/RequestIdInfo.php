<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\Common\ValueObject;

final readonly class RequestIdInfo
{
    public function __construct(
        private ?string $correlationId = null,
        private ?string $requestId = null,
    ) {
    }

    public function getCorrelationId(): ?string
    {
        return $this->correlationId;
    }

    public function getRequestId(): ?string
    {
        return $this->requestId;
    }
}
