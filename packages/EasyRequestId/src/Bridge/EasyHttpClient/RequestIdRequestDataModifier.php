<?php

declare(strict_types=1);

namespace EonX\EasyRequestId\Bridge\EasyHttpClient;

use EonX\EasyHttpClient\Interfaces\RequestDataInterface;
use EonX\EasyHttpClient\Interfaces\RequestDataModifierInterface;
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

        $headers[$correlationIdHeaderName] = $headers[$correlationIdHeaderName] ??
            $this->requestIdService->getCorrelationId();
        $headers[$requestIdHeaderName] = $headers[$requestIdHeaderName] ?? $this->requestIdService->getRequestId();

        $options['headers'] = $headers;

        return $data->setOptions($options);
    }
}
