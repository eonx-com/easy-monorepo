<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Bridge\Symfony\Factories;

use EonX\EasyPagination\Interfaces\StartSizeDataInterface;
use EonX\EasyPagination\Interfaces\StartSizeDataResolverInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class StartSizeDataFactory
{
    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    private $requestStack;

    /**
     * @var \EonX\EasyPagination\Interfaces\StartSizeDataResolverInterface
     */
    private $resolver;

    public function __construct(RequestStack $requestStack, StartSizeDataResolverInterface $resolver)
    {
        $this->requestStack = $requestStack;
        $this->resolver = $resolver;
    }

    public function __invoke(): StartSizeDataInterface
    {
        return $this->resolver->resolve($this->getRequest());
    }

    private function getFakeRequest(): Request
    {
        return new Request([], [], [], [], [], [
            'HTTP_HOST' => 'eonx.com',
        ]);
    }

    private function getRequest(): Request
    {
        return $this->requestStack->getMasterRequest() ?? $this->getFakeRequest();
    }
}
