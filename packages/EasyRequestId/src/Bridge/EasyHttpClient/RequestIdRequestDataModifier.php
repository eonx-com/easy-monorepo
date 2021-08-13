<?php

declare(strict_types=1);

namespace EonX\EasyRequestId\Bridge\EasyHttpClient;

use EonX\EasyHttpClient\Interfaces\RequestDataInterface;
use EonX\EasyHttpClient\Interfaces\RequestDataModifierInterface;
use EonX\EasyRequestId\Interfaces\RequestIdServiceInterface;

final class RequestIdRequestDataModifier implements RequestDataModifierInterface
{
    /**
     * @var \EonX\EasyRequestId\Interfaces\RequestIdServiceInterface
     */
    private $requestIdService;

    public function __construct(RequestIdServiceInterface $requestIdService)
    {
        $this->requestIdService = $requestIdService;
    }

    public function modifyRequestData(RequestDataInterface $data): RequestDataInterface
    {
        $options = $data->getOptions();
        $headers = $options['headers'] ?? [];

        $correlationIdHeaderName = $this->requestIdService->getCorrelationIdHeaderName();
        $requestIdHeaderName = $this->requestIdService->getRequestIdHeaderName();

        $headers[$correlationIdHeaderName] = $headers[$correlationIdHeaderName] ?? $this->requestIdService->getCorrelationId();
        $headers[$requestIdHeaderName] = $headers[$requestIdHeaderName] ?? $this->requestIdService->getRequestId();

        $options['headers'] = $headers;

        return $data->setOptions($options);
    }
}
