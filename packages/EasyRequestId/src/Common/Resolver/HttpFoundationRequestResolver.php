<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\Common\Resolver;

use EonX\EasyRequestId\Common\RequestId\RequestIdInterface;
use Symfony\Component\HttpFoundation\Request;

final class HttpFoundationRequestResolver
{
    public function __construct(
        private Request $request,
        private RequestIdInterface $requestId,
    ) {
    }

    /**
     * @return null[]|string[]
     */
    public function __invoke(): array
    {
        $correlationIdHeader = $this->getHeader($this->requestId->getCorrelationIdHeaderName());
        $requestIdHeader = $this->getHeader($this->requestId->getRequestIdHeaderName());

        return [
            RequestIdInterface::KEY_RESOLVED_CORRELATION_ID => $correlationIdHeader,
            RequestIdInterface::KEY_RESOLVED_REQUEST_ID => $requestIdHeader,
        ];
    }

    private function getHeader(string $header): ?string
    {
        $value = $this->request->headers->get($header);

        return \is_string($value) && $value !== '' ? $value : null;
    }
}
