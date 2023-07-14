<?php

declare(strict_types=1);

namespace EonX\EasyBugsnag\Bridge\Symfony\Session;

use Bugsnag\Client;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;
use Symfony\Component\Messenger\Event\WorkerStartedEvent;

final class SessionTrackingMessengerSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private Client $client,
    ) {
    }

    /**
     * @return string[]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            WorkerMessageReceivedEvent::class => 'onMessageReceived',
            WorkerStartedEvent::class => 'onWorkerStarted',
        ];
    }

    public function onMessageReceived(WorkerMessageReceivedEvent $event): void
    {
        $this->client->getSessionTracker()
            ->startSession();
    }

    public function onWorkerStarted(WorkerStartedEvent $event): void
    {
        // Make sure sessions are sent when worker stops
        \register_shutdown_function(function (): void {
            $this->client->getSessionTracker()
                ->sendSessions();
        });
    }
}
