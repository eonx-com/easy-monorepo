<?php
declare(strict_types=1);

namespace EonX\EasyPsr7Factory\Tests\Bridge\Symfony;

use EonX\EasyPsr7Factory\Interfaces\EasyPsr7FactoryInterface;
use EonX\EasyPsr7Factory\Tests\AbstractTestCase;
use EonX\EasyPsr7Factory\Tests\Bridge\Symfony\Stubs\KernelStub;
use EonX\EasyPsr7Factory\Tests\Bridge\Symfony\Stubs\ServiceStub;

final class EasyPsr7FactoryBundleTest extends AbstractTestCase
{
    public function testPsr7FactoryRegisteredAsService(): void
    {
        $kernel = new KernelStub();
        $kernel->boot();

        /** @var \EonX\EasyPsr7Factory\Tests\Bridge\Symfony\Stubs\ServiceStub $stub */
        $stub = $kernel->getContainer()->get(ServiceStub::class);

        self::assertInstanceOf(EasyPsr7FactoryInterface::class, $stub->getPsr7Factory());
    }
}
