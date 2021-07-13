<?php

declare(strict_types=1);

namespace EonX\EasyRequestId\Bridge\Laravel;

use EonX\EasyRequestId\Interfaces\RequestIdServiceInterface;
use EonX\EasyRequestId\Traits\ResolvesFromHttpFoundationRequest;
use Illuminate\Routing\Events\RouteMatched;

final class RequestIdRouteMatchedListener
{
    use ResolvesFromHttpFoundationRequest;

    /**
     * @var \EonX\EasyRequestId\Interfaces\RequestIdServiceInterface
     */
    private $requestIdService;

    public function __construct(RequestIdServiceInterface $requestIdService)
    {
        $this->requestIdService = $requestIdService;
    }

    public function handle(RouteMatched $event): void
    {
        $this->setResolver($event->request, $this->requestIdService);
    }
}
