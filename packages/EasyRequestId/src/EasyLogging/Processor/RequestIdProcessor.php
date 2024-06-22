<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\EasyLogging\Processor;

use EonX\EasyLogging\Config\AbstractSelfProcessorConfigProvider;
use EonX\EasyRequestId\Common\RequestId\RequestIdInterface;

final class RequestIdProcessor extends AbstractSelfProcessorConfigProvider
{
    public function __construct(
        private RequestIdInterface $requestId,
    ) {
    }

    public function __invoke(array $records): array
    {
        $extra = $records['extra'] ?? [];
        $extra[$this->requestId->getCorrelationIdHeaderName()] = $this->requestId->getCorrelationId();
        $extra[$this->requestId->getRequestIdHeaderName()] = $this->requestId->getRequestId();

        $records['extra'] = $extra;

        return $records;
    }
}
