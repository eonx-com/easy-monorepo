<?php

declare(strict_types=1);

namespace EonX\EasyRequestId\Bridge\Symfony\Messenger;

use Symfony\Component\Messenger\Stamp\StampInterface;

final class RequestIdStamp implements StampInterface
{
    /**
     * @var string
     */
    private $correlationId;

    /**
     * @var string
     */
    private $requestId;

    public function __construct(string $correlationId, string $requestId)
    {
        $this->correlationId = $correlationId;
        $this->requestId = $requestId;
    }

    public function getCorrelationId(): string
    {
        return $this->correlationId;
    }

    public function getRequestId(): string
    {
        return $this->requestId;
    }
}
