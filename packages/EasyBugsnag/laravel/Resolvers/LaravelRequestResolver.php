<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Laravel\Resolvers;

use Bugsnag\Request\RequestInterface;
use EonX\EasyBugsnag\Common\Request\HttpFoundationRequest;
use EonX\EasyBugsnag\Common\Resolver\AbstractRequestResolver;
use Illuminate\Http\Request;

final class LaravelRequestResolver extends AbstractRequestResolver
{
    public function __construct(
        private readonly Request $request,
    ) {
    }

    protected function doResolve(): RequestInterface
    {
        return new HttpFoundationRequest($this->request);
    }
}
