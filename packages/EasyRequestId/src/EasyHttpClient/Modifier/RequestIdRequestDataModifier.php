<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\EasyHttpClient\Modifier;

use EonX\EasyHttpClient\Interfaces\RequestDataInterface;
use EonX\EasyHttpClient\Interfaces\RequestDataModifierInterface;
use EonX\EasyRequestId\Common\RequestId\RequestIdInterface;

final class RequestIdRequestDataModifier implements RequestDataModifierInterface
{
    public function __construct(
        private RequestIdInterface $requestId,
    ) {
    }

    public function modifyRequestData(RequestDataInterface $data): RequestDataInterface
    {
        $options = $data->getOptions();
        $headers = $options['headers'] ?? [];

        $correlationIdHeaderName = $this->requestId->getCorrelationIdHeaderName();
        $requestIdHeaderName = $this->requestId->getRequestIdHeaderName();

        $headers[$correlationIdHeaderName] ??= $this->requestId->getCorrelationId();
        $headers[$requestIdHeaderName] ??= $this->requestId->getRequestId();

        $options['headers'] = $headers;

        return $data->setOptions($options);
    }
}
