<?php

declare(strict_types=1);

namespace EonX\EasyBugsnag\Bridge\Laravel\Session;

use Bugsnag\Client;
use Illuminate\Queue\Events\JobProcessing;

final class SessionTrackingQueueListener
{
    /**
     * @var \Bugsnag\Client
     */
    private $client;

    /**
     * @var bool
     */
    private $configured = false;

    public function __construct(Client $client)
    {
        $this->client = $client;
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
