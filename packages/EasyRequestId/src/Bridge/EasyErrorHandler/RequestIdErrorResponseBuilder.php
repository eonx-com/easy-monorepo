<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\Bridge\EasyErrorHandler;

use EonX\EasyErrorHandler\Common\Builder\AbstractErrorResponseBuilder;
use EonX\EasyErrorHandler\Common\Provider\ErrorResponseBuilderProviderInterface;
use EonX\EasyRequestId\Interfaces\RequestIdServiceInterface;
use Throwable;

final class RequestIdErrorResponseBuilder extends AbstractErrorResponseBuilder implements
    ErrorResponseBuilderProviderInterface
{
    public function __construct(
        private RequestIdServiceInterface $requestIdService,
        ?int $priority = null,
    ) {
        parent::__construct($priority);
    }

    public function buildHeaders(Throwable $throwable, ?array $headers = null): ?array
    {
        $headers ??= [];
        $headers[$this->requestIdService->getCorrelationIdHeaderName()] = $this->requestIdService->getCorrelationId();
        $headers[$this->requestIdService->getRequestIdHeaderName()] = $this->requestIdService->getRequestId();

        return parent::buildHeaders($throwable, $headers);
    }

    /**
     * @return iterable<\EonX\EasyErrorHandler\Common\Builder\ErrorResponseBuilderInterface>
     */
    public function getBuilders(): iterable
    {
        yield $this;
    }
}
