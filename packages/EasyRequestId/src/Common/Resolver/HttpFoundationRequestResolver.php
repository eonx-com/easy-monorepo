<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\Common\Resolver;

use EonX\EasyRequestId\Common\Provider\RequestIdProviderInterface;
use EonX\EasyRequestId\Common\ValueObject\RequestIdInfo;
use Symfony\Component\HttpFoundation\Request;

final readonly class HttpFoundationRequestResolver implements ResolverInterface
{
    public function __construct(
        private Request $request,
        private RequestIdProviderInterface $requestIdProvider,
    ) {
    }

    public function __invoke(): RequestIdInfo
    {
        return new RequestIdInfo(
            $this->getHeader($this->requestIdProvider->getCorrelationIdHeaderName()),
            $this->getHeader($this->requestIdProvider->getRequestIdHeaderName())
        );
    }

    private function getHeader(string $header): ?string
    {
        $value = $this->request->headers->get($header);

        return \is_string($value) && $value !== '' ? $value : null;
    }
}
