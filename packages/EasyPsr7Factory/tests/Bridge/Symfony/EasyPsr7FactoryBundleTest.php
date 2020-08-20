<?php

declare(strict_types=1);

namespace EonX\EasyPsr7Factory\Tests\Bridge\Symfony;

use EonX\EasyPsr7Factory\Interfaces\EasyPsr7FactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Request;

final class EasyPsr7FactoryBundleTest extends AbstractSymfonyTestCase
{
    public function testPsr7FactoryRegisteredAsService(): void
    {
        $request = new Request($query ?? [], [], [], [], [], ['HTTP_HOST' => 'eonx.com']);
        $container = $this->getKernel(null, $request)->getContainer();

        self::assertInstanceOf(EasyPsr7FactoryInterface::class, $container->get(EasyPsr7FactoryInterface::class));
        self::assertInstanceOf(ServerRequestInterface::class, $container->get(ServerRequestInterface::class));
    }
}
