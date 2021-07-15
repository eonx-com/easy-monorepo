<?php

declare(strict_types=1);

namespace EonX\EasyRequestId\Bridge\Symfony\Messenger;

use Symfony\Component\Messenger\Stamp\StampInterface;

final class RequestIdStamp implements StampInterface
{
    /**
     * @var null|string
     */
    private $correlationId;

    /**
     * @var null|string
     */
    private $requestId;

    public function __construct(?string $correlationId = null, ?string $requestId = null)
    {
        $this->correlationId = $correlationId;
        $this->requestId = $requestId;
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
