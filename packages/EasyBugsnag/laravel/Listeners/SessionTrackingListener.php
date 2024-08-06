<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Laravel\Listeners;

use EonX\EasyBugsnag\Laravel\Trackers\SessionTrackerTrait;
use Illuminate\Routing\Events\RouteMatched;

final class SessionTrackingListener
{
    use SessionTrackerTrait;

    public function handle(RouteMatched $event): void
    {
        $this->trackSession($event->request);
    }
}
