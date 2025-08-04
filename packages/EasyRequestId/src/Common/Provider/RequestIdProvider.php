<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\Common\Provider;

use EonX\EasyRequestId\Common\Resolver\FallbackResolverInterface;
use EonX\EasyRequestId\Common\Resolver\ResolverInterface;
use Symfony\Contracts\Service\ResetInterface;

final class RequestIdProvider implements RequestIdProviderInterface, ResetInterface
{
    private ?string $correlationId = null;

    private ?string $requestId = null;

    public function __construct(
        private readonly FallbackResolverInterface $fallbackResolver,
        private readonly string $correlationIdHeaderName,
        private readonly string $requestIdHeaderName,
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

    public function reset(): void
    {
        $this->correlationId = null;
        $this->requestId = null;
    }

    public function setResolver(ResolverInterface|callable $resolver): RequestIdProviderInterface
    {
        $requestIdInfo = $resolver();

        $this->correlationId = $requestIdInfo->getCorrelationId();
        $this->requestId = $requestIdInfo->getRequestId();

        return $this;
    }
}
