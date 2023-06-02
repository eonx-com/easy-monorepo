<?php

declare(strict_types=1);

namespace EonX\EasyRequestId\Traits;

use EonX\EasyRequestId\Interfaces\RequestIdServiceInterface;
use EonX\EasyRequestId\Resolvers\HttpFoundationRequestResolver;
use Symfony\Component\HttpFoundation\Request;

trait ResolvesFromHttpFoundationRequest
{
    private function setResolver(Request $request, RequestIdServiceInterface $requestIdService): void
    {
        $resolver = new HttpFoundationRequestResolver($request, $requestIdService);

        $requestIdService->setResolver($resolver);

        // Make sure all requests have IDs set
        $request->headers->set(
            $requestIdService->getCorrelationIdHeaderName(),
            $requestIdService->getCorrelationId(),
        );

        $request->headers->set(
            $requestIdService->getRequestIdHeaderName(),
            $requestIdService->getRequestId(),
        );
    }
}
