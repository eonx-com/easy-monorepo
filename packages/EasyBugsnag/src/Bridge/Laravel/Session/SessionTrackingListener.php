<?php

declare(strict_types=1);

namespace EonX\EasyBugsnag\Bridge\Laravel\Session;

use Bugsnag\Client;
use Illuminate\Routing\Events\RouteMatched;

final class SessionTrackingListener
{
    /**
     * @var \Bugsnag\SessionTracker
     */
    private $sessionTracker;

    public function __construct(Client $client)
    {
        $this->sessionTracker = $client->getSessionTracker();
    }

    public function handle(RouteMatched $event): void
    {
        $this->sessionTracker->startSession();

        // Not sure if there is a better way...
        \register_shutdown_function(function (): void {
            $this->sessionTracker->sendSessions();
        });
    }
}
