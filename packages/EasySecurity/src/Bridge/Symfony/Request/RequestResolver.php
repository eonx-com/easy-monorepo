<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Symfony\Request;

use EonX\EasySecurity\Interfaces\RequestResolverInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class RequestResolver implements RequestResolverInterface
{
    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function getRequest(): Request
    {
        return $this->requestStack->getMasterRequest() ?? new Request();
    }
}
