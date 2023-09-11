<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\Bridge\Symfony\Twig;

use EonX\EasyRequestId\Interfaces\RequestIdServiceInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class RequestIdTwigExtension extends AbstractExtension
{
    public function __construct(
        private RequestIdServiceInterface $requestIdService,
    ) {
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('correlationId', fn (): string => $this->requestIdService->getCorrelationId()),
            new TwigFunction(
                'correlationIdHeaderName',
                fn (): string => $this->requestIdService->getCorrelationIdHeaderName()
            ),
            new TwigFunction('requestId', fn (): string => $this->requestIdService->getRequestId()),
            new TwigFunction('requestIdHeaderName', fn (): string => $this->requestIdService->getRequestIdHeaderName()),
        ];
    }
}
