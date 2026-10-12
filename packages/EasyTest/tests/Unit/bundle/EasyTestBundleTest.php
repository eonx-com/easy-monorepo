<?php
declare(strict_types=1);

namespace EonX\EasyTest\Tests\Unit\Bundle;

use EonX\EasyTest\Messenger\Factory\InMemoryPersistentTransportFactory;
use EonX\EasyTest\Tests\Stub\Kernel\KernelStub;
use PHPUnit\Framework\TestCase;

final class EasyTestBundleTest extends TestCase
{
    public function testItRegistersThePersistentTransportFactory(): void
    {
        $kernel = new KernelStub('test', true);
        $kernel->boot();

        $factory = $kernel->getContainer()
            ->get(InMemoryPersistentTransportFactory::class);

        self::assertInstanceOf(InMemoryPersistentTransportFactory::class, $factory);
    }
}
