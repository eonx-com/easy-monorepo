<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\EasyErrorHandler\Builder;

use EonX\EasyErrorHandler\Builders\AbstractErrorResponseBuilder;
use EonX\EasyErrorHandler\Interfaces\ErrorResponseBuilderProviderInterface;
use EonX\EasyRequestId\Common\RequestId\RequestIdInterface;
use Throwable;

final class RequestIdErrorResponseBuilder extends AbstractErrorResponseBuilder implements
    ErrorResponseBuilderProviderInterface
{
    public function __construct(
        private RequestIdInterface $requestId,
        ?int $priority = null,
    ) {
        parent::__construct($priority);
    }

    public function buildHeaders(Throwable $throwable, ?array $headers = null): ?array
    {
        $headers ??= [];
        $headers[$this->requestId->getCorrelationIdHeaderName()] = $this->requestId->getCorrelationId();
        $headers[$this->requestId->getRequestIdHeaderName()] = $this->requestId->getRequestId();

        return parent::buildHeaders($throwable, $headers);
    }

    /**
     * @return iterable<\EonX\EasyErrorHandler\Interfaces\ErrorResponseBuilderInterface>
     */
    public function getBuilders(): iterable
    {
        yield $this;
    }
}
