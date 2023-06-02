<?php

declare(strict_types=1);

namespace EonX\EasyRequestId;

use EonX\EasyRequestId\Interfaces\FallbackResolverInterface;
use EonX\EasyRequestId\Interfaces\RequestIdServiceInterface;

final class RequestIdService implements RequestIdServiceInterface
{
    /**
     * @var null|string
     */
    private $correlationId;

    /**
     * @var string
     */
    private $correlationIdHeaderName;

    /**
     * @var \EonX\EasyRequestId\Interfaces\FallbackResolverInterface
     */
    private $fallback;

    /**
     * @var null|string
     */
    private $requestId;

    /**
     * @var string
     */
    private $requestIdHeaderName;

    public function __construct(
        FallbackResolverInterface $fallback,
        ?string $correlationIdHeaderName = null,
        ?string $requestIdHeaderName = null
    ) {
        $this->fallback = $fallback;
        $this->correlationIdHeaderName = $correlationIdHeaderName ?? self::DEFAULT_HTTP_HEADER_CORRELATION_ID;
        $this->requestIdHeaderName = $requestIdHeaderName ?? self::DEFAULT_HTTP_HEADER_REQUEST_ID;
    }

    public function getCorrelationId(): string
    {
        return $this->correlationId = $this->correlationId ?? $this->fallback->fallbackCorrelationId();
    }

    public function getCorrelationIdHeaderName(): string
    {
        return $this->correlationIdHeaderName;
    }

    public function getRequestId(): string
    {
        return $this->requestId = $this->requestId ?? $this->fallback->fallbackRequestId();
    }

    public function getRequestIdHeaderName(): string
    {
        return $this->requestIdHeaderName;
    }

    public function setResolver(callable $resolver): RequestIdServiceInterface
    {
        $ids = $resolver();

        $this->correlationId = $ids[self::KEY_RESOLVED_CORRELATION_ID] ?? null;
        $this->requestId = $ids[self::KEY_RESOLVED_REQUEST_ID] ?? null;

        return $this;
    }
}
