<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\Messenger\Listener;

use EonX\EasyRequestId\Common\Provider\RequestIdProviderInterface;
use EonX\EasyRequestId\Messenger\Resolver\MessengerMessageResolver;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;

final readonly class WorkerMessageReceivedListener
{
    public function __construct(
        private RequestIdProviderInterface $requestIdProvider,
    ) {
    }

    public function __invoke(WorkerMessageReceivedEvent $event): void
    {
        $this->requestIdProvider->setResolver(new MessengerMessageResolver($event->getEnvelope()));
    }
}
