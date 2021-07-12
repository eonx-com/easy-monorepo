<?php

declare(strict_types=1);

namespace EonX\EasyRequestId\Bridge\Laravel;

use EonX\EasyRequestId\Interfaces\RequestIdServiceInterface;
use EonX\EasyRequestId\Traits\ResolvesFromHttpFoundationRequest;
use Illuminate\Http\Request;

final class RequestIdMiddleware
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

    /**
     * @return mixed
     */
    public function handle(Request $request, \Closure $next)
    {
        $this->setResolver($request, $this->requestIdService);

        return $next($request);
    }
}
