<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\Bridge\EasyHttpClient;

use EonX\EasyHttpClient\Common\Modifier\RequestDataModifierInterface;
use EonX\EasyHttpClient\Common\ValueObject\RequestDataInterface;
use EonX\EasyRequestId\Interfaces\RequestIdServiceInterface;

final class RequestIdRequestDataModifier implements RequestDataModifierInterface
{
    public function __construct(
        private RequestIdServiceInterface $requestIdService,
    ) {
    }

    public function modifyRequestData(RequestDataInterface $data): RequestDataInterface
    {
        $options = $data->getOptions();
        $headers = $options['headers'] ?? [];

        $correlationIdHeaderName = $this->requestIdService->getCorrelationIdHeaderName();
        $requestIdHeaderName = $this->requestIdService->getRequestIdHeaderName();

        $headers[$correlationIdHeaderName] ??= $this->requestIdService->getCorrelationId();
        $headers[$requestIdHeaderName] ??= $this->requestIdService->getRequestId();

        $options['headers'] = $headers;

        return $data->setOptions($options);
    }
}
