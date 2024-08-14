<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Common\Listener;

use EonX\EasyBugsnag\Common\Tracker\SessionTracker;
use Symfony\Component\HttpKernel\Event\RequestEvent;

final readonly class SessionTrackingListener
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
