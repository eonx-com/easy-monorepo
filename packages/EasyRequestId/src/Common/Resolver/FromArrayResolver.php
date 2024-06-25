<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\Common\Resolver;

use EonX\EasyRequestId\Common\Provider\RequestIdProviderInterface;

final class FromArrayResolver
{
    public function __construct(
        private array $array,
        private RequestIdProviderInterface $requestIdProvider,
    ) {
    }

    /**
     * @return null[]|string[]
     */
    public function __invoke(): array
    {
        return [
            RequestIdProviderInterface::KEY_RESOLVED_CORRELATION_ID => $this->getIdValue(
                $this->requestIdProvider->getCorrelationIdHeaderName()
            ),
            RequestIdProviderInterface::KEY_RESOLVED_REQUEST_ID => $this->getIdValue(
                $this->requestIdProvider->getRequestIdHeaderName()
            ),
        ];
    }

    private function getIdValue(string $id): ?string
    {
        $value = $this->array[$id] ?? null;

        return \is_string($value) && $value !== '' ? $value : null;
    }
}
