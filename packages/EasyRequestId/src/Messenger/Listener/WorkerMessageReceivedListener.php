<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\Messenger\Listener;

use EonX\EasyRequestId\Common\RequestId\RequestIdInterface;
use EonX\EasyRequestId\Messenger\Resolver\MessengerMessageResolver;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;

final class WorkerMessageReceivedListener
{
    public function __construct(
        private RequestIdInterface $requestId,
    ) {
    }

    public function __invoke(WorkerMessageReceivedEvent $event): void
    {
        $this->requestId->setResolver(new MessengerMessageResolver($event->getEnvelope()));
    }
}
