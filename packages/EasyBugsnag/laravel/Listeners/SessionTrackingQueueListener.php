<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Laravel\Listeners;

use Bugsnag\Client;
use Illuminate\Queue\Events\JobProcessing;

final class SessionTrackingQueueListener
{
    private bool $configured = false;

    public function __construct(
        private readonly Client $client,
    ) {
    }

    public function handle(JobProcessing $event): void
    {
        $sessionTracker = $this->client->getSessionTracker();
        $sessionTracker->startSession();

        if ($this->configured) {
            return;
        }

        // Make sure sessions are sent when worker stops
        \register_shutdown_function(static function () use ($sessionTracker): void {
            $sessionTracker->sendSessions();
        });

        $this->configured = true;
    }
}
