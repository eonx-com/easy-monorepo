<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Listener;

use EonX\EasyBugsnag\Tracker\SessionTracker;
use Symfony\Component\HttpKernel\Event\RequestEvent;

final class SessionTrackingListener
{
    public function __construct(
        private SessionTracker $sessionTracker,
    ) {
    }

    public function __invoke(RequestEvent $event): void
    {
        $this->sessionTracker->startSession($event->getRequest());
    }
}
