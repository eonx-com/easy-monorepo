<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\Resolvers;

use EonX\EasyRequestId\Interfaces\RequestIdServiceInterface;

final class FromArrayResolver
{
    public function __construct(
        private array $array,
        private RequestIdServiceInterface $requestIdService,
    ) {
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
