<?php

declare(strict_types=1);

namespace EonX\EasyRequestId\Bridge\EasyLogging;

use EonX\EasyLogging\Config\AbstractSelfProcessorConfigProvider;
use EonX\EasyRequestId\Interfaces\RequestIdKeysAwareInterface;
use EonX\EasyRequestId\Interfaces\RequestIdServiceInterface;
use EonX\EasyRequestId\Traits\RequestIdKeysAwareTrait;

final class RequestIdProcessor extends AbstractSelfProcessorConfigProvider implements RequestIdKeysAwareInterface
{
    use RequestIdKeysAwareTrait;

    /**
     * @var \EonX\EasyRequestId\Interfaces\RequestIdServiceInterface
     */
    private $requestIdService;

    public function __construct(RequestIdServiceInterface $requestIdService)
    {
        $this->requestIdService = $requestIdService;
    }

    /**
     * @param mixed[] $records
     *
     * @return mixed[]
     */
    public function __invoke(array $records): array
    {
        $extra = $records['extra'] ?? [];
        $extra[$this->getRequestIdKey()] = $this->requestIdService->getRequestId();
        $extra[$this->getCorrelationIdKey()] = $this->requestIdService->getCorrelationId();

        $records['extra'] = $extra;

        return $records;
    }
}
