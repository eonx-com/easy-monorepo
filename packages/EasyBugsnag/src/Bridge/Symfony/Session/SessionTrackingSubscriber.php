<?php

declare(strict_types=1);

namespace EonX\EasyBugsnag\Bridge\Symfony\Session;

use EonX\EasyBugsnag\Session\SessionTracker;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

final class SessionTrackingSubscriber implements EventSubscriberInterface
{
    /**
     * @var \EonX\EasyBugsnag\Session\SessionTracker
     */
    private $sessionTracker;

    public function __construct(SessionTracker $sessionTracker)
    {
        $this->sessionTracker = $sessionTracker;
    }

    public function onRequest(RequestEvent $event): void
    {
        $this->sessionTracker->startSession($event->getRequest());
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
