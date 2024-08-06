<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\Common\Resolver;

use EonX\EasyRequestId\Common\Provider\RequestIdProviderInterface;
use Symfony\Component\HttpFoundation\Request;

trait HttpFoundationRequestResolverTrait
{
    private function setResolver(Request $request, RequestIdProviderInterface $requestIdProvider): void
    {
        $resolver = new HttpFoundationRequestResolver($request, $requestIdProvider);

        $requestIdProvider->setResolver($resolver);

        // Make sure all requests have IDs set
        $request->headers->set(
            $requestIdProvider->getCorrelationIdHeaderName(),
            $requestIdProvider->getCorrelationId()
        );

        $request->headers->set(
            $requestIdProvider->getRequestIdHeaderName(),
            $requestIdProvider->getRequestId()
        );
    }
}
