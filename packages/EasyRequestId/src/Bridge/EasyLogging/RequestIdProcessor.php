<?php

declare(strict_types=1);

namespace EonX\EasyRequestId\Bridge\EasyLogging;

use EonX\EasyLogging\Config\AbstractSelfProcessorConfigProvider;
use EonX\EasyRequestId\Interfaces\RequestIdServiceInterface;

final class RequestIdProcessor extends AbstractSelfProcessorConfigProvider
{
    /**
     * @var \EonX\EasyRequestId\Interfaces\RequestIdServiceInterface
     */
    private $requestIdService;

    public function __construct(RequestIdServiceInterface $requestIdService)
    {
        $this->requestIdService = $requestIdService;
    }

    /**
     * @param mixed[] $records
     *
     * @return mixed[]
     */
    public function __invoke(array $records): array
    {
        $extra = $records['extra'] ?? [];
        $extra[$this->requestIdService->getCorrelationIdHeaderName()] = $this->requestIdService->getCorrelationId();
        $extra[$this->requestIdService->getRequestIdHeaderName()] = $this->requestIdService->getRequestId();

        $records['extra'] = $extra;

        return $records;
    }
}
