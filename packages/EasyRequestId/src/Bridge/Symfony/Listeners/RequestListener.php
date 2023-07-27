<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\Bridge\Symfony\Listeners;

use EonX\EasyRequestId\Interfaces\RequestIdServiceInterface;
use EonX\EasyRequestId\Traits\ResolvesFromHttpFoundationRequest;
use Symfony\Component\HttpKernel\Event\RequestEvent;

final class RequestListener
{
    use ResolvesFromHttpFoundationRequest;

    public function __construct(
        private RequestIdServiceInterface $requestIdService,
    ) {
    }

    public function __invoke(RequestEvent $event): void
    {
        if ($event->isMainRequest()) {
            $this->setResolver($event->getRequest(), $this->requestIdService);
        }
    }
}
