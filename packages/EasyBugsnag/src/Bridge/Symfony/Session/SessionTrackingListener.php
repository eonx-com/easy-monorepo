<?php

declare(strict_types=1);

namespace EonX\EasyBugsnag\Bridge\Symfony\Session;

use EonX\EasyBugsnag\Session\SessionTracker;
use Symfony\Component\HttpKernel\Event\RequestEvent;

final class SessionTrackingListener
{
    /**
     * @var \EonX\EasyBugsnag\Session\SessionTracker
     */
    private $sessionTracker;

    public function __construct(SessionTracker $sessionTracker)
    {
        $this->sessionTracker = $sessionTracker;
    }

    public function __invoke(RequestEvent $event): void
    {
        $this->sessionTracker->startSession($event->getRequest());
    }
}
