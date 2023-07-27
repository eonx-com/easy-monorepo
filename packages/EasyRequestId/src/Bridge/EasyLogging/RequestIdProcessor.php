<?php

declare(strict_types=1);

namespace EonX\EasyRequestId\Bridge\EasyLogging;

use EonX\EasyLogging\Config\AbstractSelfProcessorConfigProvider;
use EonX\EasyRequestId\Interfaces\RequestIdServiceInterface;

final class RequestIdProcessor extends AbstractSelfProcessorConfigProvider
{
    public function __construct(
        private RequestIdServiceInterface $requestIdService,
    ) {
    }

    public function __invoke(array $records): array
    {
        $extra = $records['extra'] ?? [];
        $extra[$this->requestIdService->getCorrelationIdHeaderName()] = $this->requestIdService->getCorrelationId();
        $extra[$this->requestIdService->getRequestIdHeaderName()] = $this->requestIdService->getRequestId();

        $records['extra'] = $extra;

        return $records;
    }
}
