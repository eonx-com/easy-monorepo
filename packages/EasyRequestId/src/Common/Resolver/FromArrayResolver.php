<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\Common\Resolver;

use EonX\EasyRequestId\Common\Provider\RequestIdProviderInterface;
use EonX\EasyRequestId\Common\ValueObject\RequestIdInfo;

final class FromArrayResolver implements ResolverInterface
{
    public function __construct(
        private array $array,
        private readonly RequestIdProviderInterface $requestIdProvider,
    ) {
    }

    public function __invoke(): RequestIdInfo
    {
        return new RequestIdInfo(
            $this->getIdValue($this->requestIdProvider->getCorrelationIdHeaderName()),
            $this->getIdValue($this->requestIdProvider->getRequestIdHeaderName())
        );
    }

    private function getIdValue(string $id): ?string
    {
        $value = $this->array[$id] ?? null;

        return \is_string($value) && $value !== '' ? $value : null;
    }
}
