<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\Common\Listener;

use EonX\EasyRequestId\Common\RequestId\RequestIdInterface;
use EonX\EasyRequestId\Common\Resolver\ResolvesFromHttpFoundationRequestTrait;
use Symfony\Component\HttpKernel\Event\RequestEvent;

final class RequestListener
{
    use ResolvesFromHttpFoundationRequestTrait;

    public function __construct(
        private RequestIdInterface $requestId,
    ) {
    }

    public function __invoke(RequestEvent $event): void
    {
        if ($event->isMainRequest()) {
            $this->setResolver($event->getRequest(), $this->requestId);
        }
    }
}
