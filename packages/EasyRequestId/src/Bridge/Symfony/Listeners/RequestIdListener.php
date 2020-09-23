<?php

declare(strict_types=1);

namespace EonX\EasyRequestId\Bridge\Symfony\Listeners;

use EonX\EasyRequestId\Interfaces\RequestIdServiceInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

final class RequestIdListener
{
    /**
     * @var \EonX\EasyRequestId\Interfaces\RequestIdServiceInterface
     */
    private $requestIdService;

    public function __construct(RequestIdServiceInterface $requestIdService)
    {
        $this->requestIdService = $requestIdService;
    }

    public function __invoke(RequestEvent $event): void
    {
        if ($event->isMasterRequest()) {
            $this->requestIdService->setRequest($event->getRequest());
        }
    }
}
