<?php

declare(strict_types=1);

namespace EonX\EasyRequestId\Bridge\Laravel;

use EonX\EasyRequestId\Interfaces\RequestIdServiceInterface;
use EonX\EasyRequestId\Traits\ResolvesFromHttpFoundationRequest;
use Illuminate\Routing\Events\RouteMatched;

final class RequestIdRouteMatchedListener
{
    use ResolvesFromHttpFoundationRequest;

    public function __construct(
        private RequestIdServiceInterface $requestIdService,
    ) {
    }

    public function handle(RouteMatched $event): void
    {
        $this->setResolver($event->request, $this->requestIdService);
    }
}
