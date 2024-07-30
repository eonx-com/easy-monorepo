<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\EasyLogging\Processor;

use EonX\EasyLogging\Processor\AbstractSelfConfigProvidingProcessor;
use EonX\EasyRequestId\Common\Provider\RequestIdProviderInterface;

final class RequestIdProcessor extends AbstractSelfConfigProvidingProcessor
{
    public function __construct(
        private readonly RequestIdProviderInterface $requestIdProvider,
    ) {
    }

    public function __invoke(array $record): array
    {
        $extra = $record['extra'] ?? [];
        $extra[$this->requestIdProvider->getCorrelationIdHeaderName()] = $this->requestIdProvider->getCorrelationId();
        $extra[$this->requestIdProvider->getRequestIdHeaderName()] = $this->requestIdProvider->getRequestId();

        $record['extra'] = $extra;

        return $record;
    }
}
