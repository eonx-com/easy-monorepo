<?php

declare(strict_types=1);

namespace EonX\EasyRequestId;

use EonX\EasyRequestId\Interfaces\CorrelationIdResolverInterface;
use EonX\EasyRequestId\Interfaces\FallbackResolverInterface;
use EonX\EasyRequestId\Interfaces\RequestIdResolverInterface;
use EonX\EasyRequestId\Interfaces\RequestIdServiceInterface;
use EonX\EasyRequestId\Interfaces\ResolverInterface;
use Symfony\Component\HttpFoundation\Request;

final class RequestIdService implements RequestIdServiceInterface
{
    /**
     * @var string
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
     * @var \Symfony\Component\HttpFoundation\Request
     */
    private $request;

    /**
     * @var string
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
        Request $request,
        iterable $requestIdResolvers,
        iterable $correlationIdResolvers,
        FallbackResolverInterface $fallback
    ) {
        $this->request = $request;
        $this->requestIdResolvers = $this->filterResolvers($requestIdResolvers, RequestIdResolverInterface::class);
        $this->correlationIdResolvers = $this->filterResolvers(
            $correlationIdResolvers,
            CorrelationIdResolverInterface::class
        );
        $this->fallback = $fallback;
    }

    public function getCorrelationId(): string
    {
        if ($this->correlationId !== null) {
            return $this->correlationId;
        }

        foreach ($this->correlationIdResolvers as $resolver) {
            $correlationId = $resolver->getCorrelationId($this->request);

            if ($correlationId !== null) {
                break;
            }
        }

        return $this->correlationId = $correlationId ?? $this->fallback->fallbackCorrelationId();
    }

    public function getRequestId(): string
    {
        if ($this->requestId !== null) {
            return $this->requestId;
        }

        foreach ($this->requestIdResolvers as $resolver) {
            $requestId = $resolver->getRequestId($this->request);

            if ($requestId !== null) {
                break;
            }
        }

        return $this->requestId = $requestId ?? $this->fallback->fallbackRequestId();
    }

    /**
     * @param iterable<mixed> $resolvers
     *
     * @return \EonX\EasyRequestId\Interfaces\ResolverInterface[]
     */
    private function filterResolvers(iterable $resolvers, string $class): array
    {
        $resolvers = $resolvers instanceof \Traversable ? \iterator_to_array($resolvers) : (array)$resolvers;

        $resolvers = \array_filter($resolvers, static function ($resolver) use ($class): bool {
            return $resolver instanceof $class;
        });

        \usort($resolvers, static function (ResolverInterface $first, ResolverInterface $second): int {
            return $first->getPriority() <=> $second->getPriority();
        });

        return $resolvers;
    }
}
