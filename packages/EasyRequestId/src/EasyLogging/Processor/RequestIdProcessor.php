<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\EasyLogging\Processor;

use EonX\EasyLogging\Processor\AbstractSelfConfigProvidingProcessor;
use EonX\EasyRequestId\Common\Provider\RequestIdProviderInterface;
use Monolog\Attribute\AsMonologProcessor;
use Monolog\LogRecord;

#[AsMonologProcessor]
final class RequestIdProcessor extends AbstractSelfConfigProvidingProcessor
{
    public function __construct(
        private readonly RequestIdProviderInterface $requestIdProvider,
    ) {
    }

    public function __invoke(LogRecord $record): LogRecord
    {
        $extra = $record->extra;
        $extra[$this->requestIdProvider->getCorrelationIdHeaderName()] = $this->requestIdProvider->getCorrelationId();
        $extra[$this->requestIdProvider->getRequestIdHeaderName()] = $this->requestIdProvider->getRequestId();

        return $record->with(extra: $extra);
    }
}
