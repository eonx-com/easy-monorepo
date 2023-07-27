<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Bridge\Laravel\Session;

use Illuminate\Routing\Events\RouteMatched;

final class SessionTrackingListener
{
    use TracksSessionTrait;

    public function handle(RouteMatched $event): void
    {
        $this->trackSession($event->request);
    }
}
