<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Bridge\Symfony\Factories;

use EonX\EasyPsr7Factory\Interfaces\EasyPsr7FactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\RequestStack;

final class ServerRequestFactory
{
    /**
     * @var \EonX\EasyPsr7Factory\Interfaces\EasyPsr7FactoryInterface
     */
    private $psr7Factory;

    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    private $requestStack;

    public function __construct(EasyPsr7FactoryInterface $psr7Factory, RequestStack $requestStack)
    {
        $this->psr7Factory = $psr7Factory;
        $this->requestStack = $requestStack;
    }

    public function __invoke(): ServerRequestInterface
    {
        return $this->psr7Factory->createRequest($this->requestStack->getMasterRequest());
    }
}
