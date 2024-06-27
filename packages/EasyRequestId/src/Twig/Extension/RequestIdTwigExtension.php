<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\Twig\Extension;

use EonX\EasyRequestId\Common\Provider\RequestIdProviderInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class RequestIdTwigExtension extends AbstractExtension
{
    public function __construct(
        private RequestIdProviderInterface $requestIdProvider,
    ) {
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('correlationId', fn (): string => $this->requestIdProvider->getCorrelationId()),
            new TwigFunction(
                'correlationIdHeaderName',
                fn (): string => $this->requestIdProvider->getCorrelationIdHeaderName()
            ),
            new TwigFunction('requestId', fn (): string => $this->requestIdProvider->getRequestId()),
            new TwigFunction(
                'requestIdHeaderName',
                fn (): string => $this->requestIdProvider->getRequestIdHeaderName()
            ),
        ];
    }
}
