<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\Common\Resolver;

use EonX\EasyRequestId\Common\RequestId\RequestIdInterface;
use Symfony\Component\HttpFoundation\Request;

trait ResolvesFromHttpFoundationRequestTrait
{
    private function setResolver(Request $request, RequestIdInterface $requestId): void
    {
        $resolver = new HttpFoundationRequestResolver($request, $requestId);

        $requestId->setResolver($resolver);

        // Make sure all requests have IDs set
        $request->headers->set(
            $requestId->getCorrelationIdHeaderName(),
            $requestId->getCorrelationId()
        );

        $request->headers->set(
            $requestId->getRequestIdHeaderName(),
            $requestId->getRequestId()
        );
    }
}
