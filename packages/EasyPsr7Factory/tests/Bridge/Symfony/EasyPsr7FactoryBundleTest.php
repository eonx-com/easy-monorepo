<?php

declare(strict_types=1);

namespace EonX\EasyPsr7Factory\Tests\Bridge\Symfony;

use EonX\EasyPsr7Factory\Interfaces\EasyPsr7FactoryInterface;
use Psr\Http\Message\ServerRequestInterface;

final class EasyPsr7FactoryBundleTest extends AbstractSymfonyTestCase
{
    public function testPsr7FactoryRegisteredAsService(): void
    {
        $container = $this->getKernel()
            ->getContainer();

        self::assertInstanceOf(EasyPsr7FactoryInterface::class, $container->get(EasyPsr7FactoryInterface::class));
        self::assertInstanceOf(ServerRequestInterface::class, $container->get(ServerRequestInterface::class));
    }
}
