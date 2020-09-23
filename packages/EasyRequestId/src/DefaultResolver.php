<?php

declare(strict_types=1);

namespace EonX\EasyRequestId;

use EonX\EasyRequestId\Interfaces\CorrelationIdResolverInterface;
use EonX\EasyRequestId\Interfaces\RequestIdKeysAwareInterface;
use EonX\EasyRequestId\Interfaces\RequestIdResolverInterface;
use Symfony\Component\HttpFoundation\Request;

final class DefaultResolver implements CorrelationIdResolverInterface, RequestIdResolverInterface
{
    /**
     * @var string
     */
    private $correlationIdHeader;

    /**
     * @var string
     */
    private $priority;

    /**
     * @var string
     */
    private $requestIdHeader;

    public function __construct(
        ?string $requestIdHeader = null,
        ?string $correlationIdHeader = null,
        ?int $priority = null
    ) {
        $this->requestIdHeader = $requestIdHeader ?? RequestIdKeysAwareInterface::KEY_REQUEST_ID;
        $this->correlationIdHeader = $correlationIdHeader ?? RequestIdKeysAwareInterface::KEY_CORRELATION_ID;
        $this->priority = $priority ?? 0;
    }

    public function getCorrelationId(Request $request): ?string
    {
        return $this->getHeader($request, $this->correlationIdHeader);
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function getRequestId(Request $request): ?string
    {
        return $this->getHeader($request, $this->requestIdHeader);
    }

    private function getHeader(Request $request, string $header): ?string
    {
        $value = $request->headers->get($header);

        return empty($value) === false ? (string)$value : null;
    }
}
