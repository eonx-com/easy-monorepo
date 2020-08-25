<?php

declare(strict_types=1);

namespace EonX\EasyBugsnag\Bridge\Laravel\Request;

use Bugsnag\Request\RequestInterface;
use EonX\EasyBugsnag\Request\AbstractRequestResolver;
use EonX\EasyBugsnag\Request\HttpFoundationRequest;
use Illuminate\Http\Request;

final class LaravelRequestResolver extends AbstractRequestResolver
{
    /**
     * @var \Illuminate\Http\Request
     */
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    protected function doResolve(): RequestInterface
    {
        return new HttpFoundationRequest($this->request);
    }
}
