<?php

declare(strict_types=1);

namespace EonX\EasyRequestId;

use EonX\EasyRequestId\Interfaces\CorrelationIdResolverInterface;
use EonX\EasyRequestId\Interfaces\FallbackResolverInterface;
use EonX\EasyRequestId\Interfaces\RequestIdResolverInterface;
use EonX\EasyRequestId\Interfaces\RequestIdServiceInterface;
use EonX\EasyUtils\CollectorHelper;
use Symfony\Component\HttpFoundation\Request;

final class RequestIdService implements RequestIdServiceInterface
{
    /**
     * @var null|string
     */
    private $correlationId;

    /**
     * @var \EonX\EasyRequestId\Interfaces\CorrelationIdResolverInterface[]
     */
    private $correlationIdResolvers;

    /**
     * @var \EonX\EasyRequestId\Interfaces\FallbackResolverInterface
     */
    private $fallback;

    /**
     * @var null|string
     */
    private $requestId;

    /**
     * @var \EonX\EasyRequestId\Interfaces\RequestIdResolverInterface[]
     */
    private $requestIdResolvers;

    /**
     * @param iterable<mixed> $requestIdResolvers
     * @param iterable<mixed> $correlationIdResolvers
     */
    public function __construct(
        iterable $requestIdResolvers,
        iterable $correlationIdResolvers,
        FallbackResolverInterface $fallback
    ) {
        $this->requestIdResolvers = $this->filterResolvers($requestIdResolvers, RequestIdResolverInterface::class);
        $this->correlationIdResolvers = $this->filterResolvers(
            $correlationIdResolvers,
            CorrelationIdResolverInterface::class
        );
        $this->fallback = $fallback;
    }

    public function getCorrelationId(): string
    {
        return $this->correlationId = $this->correlationId ?? $this->fallback->fallbackCorrelationId();
    }

    public function getRequestId(): string
    {
        return $this->requestId = $this->requestId ?? $this->fallback->fallbackRequestId();
    }

    public function setRequest(Request $request): RequestIdServiceInterface
    {
        // Reset ids
        $this->correlationId = null;
        $this->requestId = null;

        foreach ($this->correlationIdResolvers as $resolver) {
            $correlationId = $resolver->getCorrelationId($request);

            if ($correlationId !== null) {
                $this->correlationId = $correlationId;

                break;
            }
        }

        foreach ($this->requestIdResolvers as $resolver) {
            $requestId = $resolver->getRequestId($request);

            if ($requestId !== null) {
                $this->requestId = $requestId;

                break;
            }
        }

        return $this;
    }

    /**
     * @param iterable<mixed> $resolvers
     * @param class-string $class
     *
     * @return \EonX\EasyRequestId\Interfaces\ResolverInterface[]
     */
    private function filterResolvers(iterable $resolvers, string $class): array
    {
        return CollectorHelper::orderLowerPriorityFirst(CollectorHelper::filterByClass($resolvers, $class));
    }
}
