<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\Messenger\Listener;

use EonX\EasyRequestId\Common\Provider\RequestIdProviderInterface;
use EonX\EasyRequestId\Messenger\Stamp\RequestIdStamp;
use Symfony\Component\Messenger\Event\SendMessageToTransportsEvent;

final class SendMessageToTransportsListener
{
    public function __construct(
        private RequestIdProviderInterface $requestIdProvider,
    ) {
    }

    public function __invoke(SendMessageToTransportsEvent $event): void
    {
        $event->setEnvelope($event->getEnvelope()->with(new RequestIdStamp(
            $this->requestIdProvider->getCorrelationId(),
            $this->requestIdProvider->getRequestId()
        )));
    }
}
