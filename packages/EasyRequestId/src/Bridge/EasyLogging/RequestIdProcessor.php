<?php

declare(strict_types=1);

namespace EonX\EasyRequestId\Bridge\EasyLogging;

use EonX\EasyLogging\Config\AbstractSelfProcessorConfigProvider;
use EonX\EasyRequestId\DeferredRequestIdServiceProvider;
use EonX\EasyRequestId\Interfaces\RequestIdKeysAwareInterface;
use EonX\EasyRequestId\Traits\RequestIdKeysAwareTrait;

final class RequestIdProcessor extends AbstractSelfProcessorConfigProvider implements RequestIdKeysAwareInterface
{
    use RequestIdKeysAwareTrait;

    /**
     * @var \EonX\EasyRequestId\DeferredRequestIdServiceProvider
     */
    private $deferred;

    public function __construct(DeferredRequestIdServiceProvider $deferred)
    {
        $this->deferred = $deferred;
    }

    /**
     * @param mixed[] $records
     *
     * @return mixed[]
     */
    public function __invoke(array $records): array
    {
        $service = $this->deferred->getRequestIdService();

        $extra = $records['extra'] ?? [];
        $extra[$this->getRequestIdKey()] = $service->getRequestId();
        $extra[$this->getCorrelationIdKey()] = $service->getCorrelationId();

        $records['extra'] = $extra;

        return $records;
    }
}
