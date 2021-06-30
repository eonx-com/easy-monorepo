<?php

declare(strict_types=1);

namespace EonX\EasyBugsnag\Bridge\Laravel\Session;

use EonX\EasyBugsnag\Session\SessionTracker;
use Illuminate\Http\Request;

trait TracksSessionTrait
{
    /**
     * @var \EonX\EasyBugsnag\Session\SessionTracker
     */
    private $sessionTracker;

    public function __construct(SessionTracker $sessionTracker)
    {
        $this->sessionTracker = $sessionTracker;
    }

    private function trackSession(Request $request): void
    {
        $this->sessionTracker->startSession($request);

        // Not sure if there is a better way...
        \register_shutdown_function(function (): void {
            $this->sessionTracker->sendSessions();
        });
    }
}
