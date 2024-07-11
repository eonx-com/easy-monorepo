<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\EasyLogging\Processor;

use EonX\EasyLogging\Processor\AbstractSelfConfigProvidingProcessor;
use EonX\EasyRequestId\Common\Provider\RequestIdProviderInterface;
use Monolog\LogRecord;

final class RequestIdProcessor extends AbstractSelfConfigProvidingProcessor
{
    public function __construct(
        private RequestIdProviderInterface $requestIdProvider,
    ) {
    }

    public function __invoke(LogRecord $record): LogRecord
    {
        $record->extra[$this->requestIdProvider->getCorrelationIdHeaderName()]
            = $this->requestIdProvider->getCorrelationId();
        $record->extra[$this->requestIdProvider->getRequestIdHeaderName()] = $this->requestIdProvider->getRequestId();

        return $record;
    }
}
