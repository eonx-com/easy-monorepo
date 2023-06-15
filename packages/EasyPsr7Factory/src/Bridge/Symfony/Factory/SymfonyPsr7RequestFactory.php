<?php

declare(strict_types=1);

namespace EonX\EasyPsr7Factory\Bridge\Symfony\Factory;

use EonX\EasyPsr7Factory\Interfaces\EasyPsr7FactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class SymfonyPsr7RequestFactory
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
        return $this->psr7Factory->createRequest($this->getRequest());
    }

    private function getFakeRequest(): Request
    {
        return new Request([], [], [], [], [], [
            'HTTP_HOST' => 'eonx.com',
        ]);
    }

    private function getRequest(): Request
    {
        // Fake request when running in console
        if (\getenv('LINES') && \getenv('COLUMNS')) {
            return $this->getFakeRequest();
        }

        return $this->requestStack->getMainRequest() ?? $this->getFakeRequest();
    }
}
