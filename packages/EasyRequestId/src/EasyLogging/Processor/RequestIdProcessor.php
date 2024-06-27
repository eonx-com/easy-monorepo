<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\EasyLogging\Processor;

use EonX\EasyLogging\Config\AbstractSelfProcessorConfigProvider;
use EonX\EasyRequestId\Common\Provider\RequestIdProviderInterface;

final class RequestIdProcessor extends AbstractSelfProcessorConfigProvider
{
    public function __construct(
        private RequestIdProviderInterface $requestIdProvider,
    ) {
    }

    public function __invoke(array $records): array
    {
        $extra = $records['extra'] ?? [];
        $extra[$this->requestIdProvider->getCorrelationIdHeaderName()] = $this->requestIdProvider->getCorrelationId();
        $extra[$this->requestIdProvider->getRequestIdHeaderName()] = $this->requestIdProvider->getRequestId();

        $records['extra'] = $extra;

        return $records;
    }
}
