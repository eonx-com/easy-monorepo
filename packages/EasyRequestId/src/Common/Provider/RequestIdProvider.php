<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\Common\Provider;

use EonX\EasyRequestId\Common\Resolver\FallbackResolverInterface;
use EonX\EasyRequestId\Common\Resolver\ResolverInterface;

final class RequestIdProvider implements RequestIdProviderInterface
{
    private ?string $correlationId = null;

    private ?string $requestId = null;

    public function __construct(
        private FallbackResolverInterface $fallbackResolver,
        private string $correlationIdHeaderName,
        private string $requestIdHeaderName,
    ) {
    }

    public function getCorrelationId(): string
    {
        return $this->correlationId ??= $this->fallbackResolver->fallbackCorrelationId();
    }

    public function getCorrelationIdHeaderName(): string
    {
        return $this->correlationIdHeaderName;
    }

    public function getRequestId(): string
    {
        return $this->requestId ??= $this->fallbackResolver->fallbackRequestId();
    }

    public function getRequestIdHeaderName(): string
    {
        return $this->requestIdHeaderName;
    }

    public function setResolver(ResolverInterface|callable $resolver): RequestIdProviderInterface
    {
        $requestIdInfo = $resolver();

        $this->correlationId = $requestIdInfo->getCorrelationId();
        $this->requestId = $requestIdInfo->getRequestId();

        return $this;
    }
}
