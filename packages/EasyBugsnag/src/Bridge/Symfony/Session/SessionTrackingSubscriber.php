<?php

declare(strict_types=1);

namespace EonX\EasyBugsnag\Bridge\Symfony\Session;

use Bugsnag\Client;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

final class SessionTrackingSubscriber implements EventSubscriberInterface
{
    /**
     * @var \Bugsnag\SessionTracker
     */
    private $sessionTracker;

    public function __construct(Client $client)
    {
        $this->sessionTracker = $client->getSessionTracker();
    }

    public function onRequest(RequestEvent $event): void
    {
        $this->sessionTracker->startSession();
    }

    public function onTerminate(TerminateEvent $event): void
    {
        $this->sessionTracker->sendSessions();
    }

    /**
     * @return string[]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => 'onRequest',
            TerminateEvent::class => 'onTerminate',
        ];
    }
}
