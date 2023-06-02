<?php

declare(strict_types=1);

namespace EonX\EasyRequestId\Resolvers;

use EonX\EasyRequestId\Interfaces\RequestIdServiceInterface;

final class FromArrayResolver
{
    /**
     * @var mixed[]
     */
    private $array;

    /**
     * @var \EonX\EasyRequestId\Interfaces\RequestIdServiceInterface
     */
    private $requestIdService;

    /**
     * @param mixed[] $array
     */
    public function __construct(array $array, RequestIdServiceInterface $requestIdService)
    {
        $this->array = $array;
        $this->requestIdService = $requestIdService;
    }

    /**
     * @return null[]|string[]
     */
    public function __invoke(): array
    {
        return [
            RequestIdServiceInterface::KEY_RESOLVED_CORRELATION_ID => $this->getIdValue(
                $this->requestIdService->getCorrelationIdHeaderName()
            ),
            RequestIdServiceInterface::KEY_RESOLVED_REQUEST_ID => $this->getIdValue(
                $this->requestIdService->getRequestIdHeaderName()
            ),
        ];
    }

    private function getIdValue(string $id): ?string
    {
        $value = $this->array[$id] ?? null;

        return \is_string($value) && $value !== '' ? $value : null;
    }
}
