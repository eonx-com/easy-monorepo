<?php

declare(strict_types=1);

namespace EonX\EasyRequestId\Interfaces;

interface RequestIdKeysAwareInterface
{
    /**
     * @var string
     */
    public const KEY_CORRELATION_ID = 'X-EONX-CORRELATION-ID';

    /**
     * @var string
     */
    public const KEY_REQUEST_ID = 'X-EONX-REQUEST-ID';

    public function setCorrelationIdKey(string $correlationIdKey): void;

    public function setRequestIdKey(string $requestIdKey): void;
}
