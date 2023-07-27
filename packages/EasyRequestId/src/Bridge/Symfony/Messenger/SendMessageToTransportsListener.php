<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\Bridge\Symfony\Messenger;

use EonX\EasyRequestId\Interfaces\RequestIdServiceInterface;
use Symfony\Component\Messenger\Event\SendMessageToTransportsEvent;

final class SendMessageToTransportsListener
{
    public function __construct(
        private RequestIdServiceInterface $requestIdService,
    ) {
    }

    public function __invoke(SendMessageToTransportsEvent $event): void
    {
        $event->setEnvelope($event->getEnvelope()->with(new RequestIdStamp(
            $this->requestIdService->getCorrelationId(),
            $this->requestIdService->getRequestId()
        )));
    }
}
