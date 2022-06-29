<?php

declare(strict_types=1);

namespace EonX\EasyBugsnag\Bridge\Symfony\Request;

use Bugsnag\Request\NullRequest;
use Bugsnag\Request\RequestInterface;
use EonX\EasyBugsnag\Request\AbstractRequestResolver;
use EonX\EasyBugsnag\Request\HttpFoundationRequest;
use Symfony\Component\HttpFoundation\RequestStack;

final class SymfonyRequestResolver extends AbstractRequestResolver
{
    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    protected function doResolve(): RequestInterface
    {
        $requestGetter = \method_exists($this->requestStack, 'getMainRequest')
            ? 'getMainRequest'
            : 'getMasterRequest';

        $request = $this->requestStack->{$requestGetter}();

        return $request ? new HttpFoundationRequest($request) : new NullRequest();
    }
}
