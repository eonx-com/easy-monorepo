<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\Common\RequestId;

use EonX\EasyRequestId\Common\Resolver\FallbackResolverInterface;

final class RequestId implements RequestIdInterface
{
    private ?string $correlationId = null;

    private string $correlationIdHeaderName;

    private ?string $requestId = null;

    private string $requestIdHeaderName;

    public function __construct(
        private FallbackResolverInterface $fallback,
        ?string $correlationIdHeaderName = null,
        ?string $requestIdHeaderName = null,
    ) {
        $this->correlationIdHeaderName = $correlationIdHeaderName ?? self::DEFAULT_HTTP_HEADER_CORRELATION_ID;
        $this->requestIdHeaderName = $requestIdHeaderName ?? self::DEFAULT_HTTP_HEADER_REQUEST_ID;
    }

    public function getCorrelationId(): string
    {
        return $this->correlationId ??= $this->fallback->fallbackCorrelationId();
    }

    public function getCorrelationIdHeaderName(): string
    {
        return $this->correlationIdHeaderName;
    }

    public function getRequestId(): string
    {
        return $this->requestId ??= $this->fallback->fallbackRequestId();
    }

    public function getRequestIdHeaderName(): string
    {
        return $this->requestIdHeaderName;
    }

    public function setResolver(callable $resolver): RequestIdInterface
    {
        $ids = $resolver();

        $this->correlationId = $ids[self::KEY_RESOLVED_CORRELATION_ID] ?? null;
        $this->requestId = $ids[self::KEY_RESOLVED_REQUEST_ID] ?? null;

        return $this;
    }
}
