<?php

declare(strict_types=1);

namespace EonX\EasyBugsnag\Bridge\Symfony\Session;

use EonX\EasyBugsnag\Session\SessionTracker;
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
