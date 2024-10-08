<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Common\Resolver;

use Bugsnag\Request\NullRequest;
use Bugsnag\Request\RequestInterface;
use EonX\EasyBugsnag\Common\Request\HttpFoundationRequest;
use Symfony\Component\HttpFoundation\RequestStack;

final class SymfonyRequestResolver extends AbstractRequestResolver
{
    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    protected function doResolve(): RequestInterface
    {
        $request = $this->requestStack->getMainRequest();

        return $request !== null ? new HttpFoundationRequest($request) : new NullRequest();
    }
}
