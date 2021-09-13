<?php

declare(strict_types=1);

namespace EonX\EasyRequestId\Bridge\Symfony\Twig;

use EonX\EasyRequestId\Interfaces\RequestIdServiceInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class RequestIdTwigExtension extends AbstractExtension
{
    /**
     * @var \EonX\EasyRequestId\Interfaces\RequestIdServiceInterface
     */
    private $requestIdService;

    public function __construct(RequestIdServiceInterface $requestIdService)
    {
        $this->requestIdService = $requestIdService;
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('correlationId', function (): string {
                return $this->requestIdService->getCorrelationId();
            }),
            new TwigFunction('correlationIdHeaderName', function (): string {
                return $this->requestIdService->getCorrelationIdHeaderName();
            }),
            new TwigFunction('requestId', function (): string {
                return $this->requestIdService->getRequestId();
            }),
            new TwigFunction('requestIdHeaderName', function (): string {
                return $this->requestIdService->getRequestIdHeaderName();
            }),
        ];
    }
}
