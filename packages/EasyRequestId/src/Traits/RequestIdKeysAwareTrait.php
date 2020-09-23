<?php

declare(strict_types=1);

namespace EonX\EasyRequestId\Traits;

use EonX\EasyRequestId\Interfaces\RequestIdServiceInterface;

trait RequestIdKeysAwareTrait
{
    /**
     * @var string
     */
    private $correlationIdKey;

    /**
     * @var string
     */
    private $requestIdKey;

    public function setCorrelationIdKey(string $correlationIdKey): void
    {
        $this->correlationIdKey = $correlationIdKey;
    }

    public function setRequestIdKey(string $requestIdKey): void
    {
        $this->requestIdKey = $requestIdKey;
    }

    protected function getCorrelationIdKey(): string
    {
        return $this->correlationIdKey ?? RequestIdServiceInterface::KEY_CORRELATION_ID;
    }

    protected function getRequestIdKey(): string
    {
        return $this->requestIdKey ?? RequestIdServiceInterface::KEY_REQUEST_ID;
    }
}
