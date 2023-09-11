<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\Bridge\Symfony\Messenger;

use EonX\EasyRequestId\Interfaces\RequestIdServiceInterface;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;

final class WorkerMessageReceivedListener
{
    public function __construct(
        private RequestIdServiceInterface $requestIdService,
    ) {
    }

    public function __invoke(WorkerMessageReceivedEvent $event): void
    {
        $this->requestIdService->setResolver(new MessengerMessageResolver($event->getEnvelope()));
    }
}
