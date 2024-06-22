<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\Common\Resolver;

use EonX\EasyRequestId\Common\RequestId\RequestIdInterface;

final class FromArrayResolver
{
    public function __construct(
        private array $array,
        private RequestIdInterface $requestId,
    ) {
    }

    /**
     * @return null[]|string[]
     */
    public function __invoke(): array
    {
        return [
            RequestIdInterface::KEY_RESOLVED_CORRELATION_ID => $this->getIdValue(
                $this->requestId->getCorrelationIdHeaderName()
            ),
            RequestIdInterface::KEY_RESOLVED_REQUEST_ID => $this->getIdValue(
                $this->requestId->getRequestIdHeaderName()
            ),
        ];
    }

    private function getIdValue(string $id): ?string
    {
        $value = $this->array[$id] ?? null;

        return \is_string($value) && $value !== '' ? $value : null;
    }
}
