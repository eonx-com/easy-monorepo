<?php

declare(strict_types=1);

namespace EonX\EasyRequestId\Bridge\Symfony\Factories;

use EonX\EasyRequestId\Interfaces\FallbackResolverInterface;
use EonX\EasyRequestId\Interfaces\RequestIdServiceInterface;
use EonX\EasyRequestId\RequestIdService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class RequestIdServiceFactory
{
    /**
     * @var iterable<\EonX\EasyRequestId\Interfaces\CorrelationIdResolverInterface>
     */
    private $correlationIdResolvers;

    /**
     * @var \EonX\EasyRequestId\Interfaces\FallbackResolverInterface
     */
    private $fallback;

    /**
     * @var iterable<\EonX\EasyRequestId\Interfaces\RequestIdResolverInterface>
     */
    private $requestIdResolvers;

    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    private $requestStack;

    /**
     * @param iterable<\EonX\EasyRequestId\Interfaces\CorrelationIdResolverInterface> $correlationIdResolvers
     * @param iterable<\EonX\EasyRequestId\Interfaces\RequestIdResolverInterface> $requestIdResolvers
     */
    public function __construct(
        RequestStack $requestStack,
        iterable $correlationIdResolvers,
        iterable $requestIdResolvers,
        FallbackResolverInterface $fallback
    ) {
        $this->requestStack = $requestStack;
        $this->correlationIdResolvers = $correlationIdResolvers;
        $this->requestIdResolvers = $requestIdResolvers;
        $this->fallback = $fallback;
    }

    public function __invoke(): RequestIdServiceInterface
    {
        return new RequestIdService(
            $this->requestStack->getMasterRequest() ?? new Request(),
            $this->requestIdResolvers,
            $this->correlationIdResolvers,
            $this->fallback
        );
    }
}
