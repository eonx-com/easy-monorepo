<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Bridge\Laravel\Session;

use EonX\EasyBugsnag\Session\SessionTracker;
use Illuminate\Http\Request;

trait TracksSessionTrait
{
    public function __construct(
        private SessionTracker $sessionTracker,
    ) {
    }

    private function trackSession(Request $request): void
    {
        $this->sessionTracker->startSession($request);
    }
}
