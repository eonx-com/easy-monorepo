<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\Common\Resolver;

use EonX\EasyRequestId\Common\Provider\RequestIdProviderInterface;
use Symfony\Component\HttpFoundation\Request;

final class HttpFoundationRequestResolver
{
    public function __construct(
        private Request $request,
        private RequestIdProviderInterface $requestIdProvider,
    ) {
    }

    /**
     * @return null[]|string[]
     */
    public function __invoke(): array
    {
        $correlationIdHeader = $this->getHeader($this->requestIdProvider->getCorrelationIdHeaderName());
        $requestIdHeader = $this->getHeader($this->requestIdProvider->getRequestIdHeaderName());

        return [
            RequestIdProviderInterface::KEY_RESOLVED_CORRELATION_ID => $correlationIdHeader,
            RequestIdProviderInterface::KEY_RESOLVED_REQUEST_ID => $requestIdHeader,
        ];
    }

    private function getHeader(string $header): ?string
    {
        $value = $this->request->headers->get($header);

        return \is_string($value) && $value !== '' ? $value : null;
    }
}
