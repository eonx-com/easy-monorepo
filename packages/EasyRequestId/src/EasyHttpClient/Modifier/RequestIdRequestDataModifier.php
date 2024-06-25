<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\EasyHttpClient\Modifier;

use EonX\EasyHttpClient\Common\Modifier\RequestDataModifierInterface;
use EonX\EasyHttpClient\Common\ValueObject\RequestDataInterface;
use EonX\EasyRequestId\Common\Provider\RequestIdProviderInterface;

final class RequestIdRequestDataModifier implements RequestDataModifierInterface
{
    public function __construct(
        private RequestIdProviderInterface $requestIdProvider,
    ) {
    }

    public function modifyRequestData(RequestDataInterface $data): RequestDataInterface
    {
        $options = $data->getOptions();
        $headers = $options['headers'] ?? [];

        $correlationIdHeaderName = $this->requestIdProvider->getCorrelationIdHeaderName();
        $requestIdHeaderName = $this->requestIdProvider->getRequestIdHeaderName();

        $headers[$correlationIdHeaderName] ??= $this->requestIdProvider->getCorrelationId();
        $headers[$requestIdHeaderName] ??= $this->requestIdProvider->getRequestId();

        $options['headers'] = $headers;

        return $data->setOptions($options);
    }
}
