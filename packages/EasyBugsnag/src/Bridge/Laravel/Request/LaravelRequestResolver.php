<?php

declare(strict_types=1);

namespace EonX\EasyBugsnag\Bridge\Laravel\Request;

use Bugsnag\Request\RequestInterface;
use EonX\EasyBugsnag\Request\AbstractRequestResolver;
use EonX\EasyBugsnag\Request\HttpFoundationRequest;
use Illuminate\Http\Request;

final class LaravelRequestResolver extends AbstractRequestResolver
{
    public function __construct(
        private Request $request,
    ) {
    }

    protected function doResolve(): RequestInterface
    {
        return new HttpFoundationRequest($this->request);
    }
}
