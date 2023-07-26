<?php

declare(strict_types=1);

namespace EonX\EasyRequestId\Bridge\Symfony\Messenger;

use Symfony\Component\Messenger\Stamp\StampInterface;

final class RequestIdStamp implements StampInterface
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
