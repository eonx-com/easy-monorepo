<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\Common\Listener;

use EonX\EasyRequestId\Common\Provider\RequestIdProviderInterface;
use EonX\EasyRequestId\Common\Resolver\HttpFoundationRequestResolverTrait;
use Symfony\Component\HttpKernel\Event\RequestEvent;

final class RequestListener
{
    use HttpFoundationRequestResolverTrait;

    public function __construct(
        private RequestIdProviderInterface $requestIdProvider,
    ) {
    }

    public function __invoke(RequestEvent $event): void
    {
        if ($event->isMainRequest()) {
            $this->setResolver($event->getRequest(), $this->requestIdProvider);
        }
    }
}
