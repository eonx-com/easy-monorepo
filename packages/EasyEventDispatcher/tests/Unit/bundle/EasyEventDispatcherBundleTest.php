<?php
declare(strict_types=1);

namespace EonX\EasyEventDispatcher\Tests\Unit\Bundle;

use EonX\EasyEventDispatcher\Dispatcher\EventDispatcherInterface;
use EonX\EasyEventDispatcher\Tests\Stub\Dispatcher\SymfonyEventDispatcherStub;
use EonX\EasyEventDispatcher\Tests\Stub\Event\EventStub;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface as SymfonyEventDispatcherInterface;

final class EasyEventDispatcherBundleTest extends AbstractSymfonyTestCase
{
    public function testDispatchInSymfony(): void
    {
        $container = $this->getKernel()
            ->getContainer();
        /** @var \EonX\EasyEventDispatcher\Tests\Stub\Dispatcher\SymfonyEventDispatcherStub $symfonyDispatcher */
        $symfonyDispatcher = $container->get(SymfonyEventDispatcherInterface::class);
        $easyDispatcher = $container->get(EventDispatcherInterface::class);
        $event = new EventStub();

        $dispatched = $easyDispatcher->dispatch($event);

        self::assertInstanceOf(SymfonyEventDispatcherStub::class, $symfonyDispatcher);
        self::assertNotEmpty($symfonyDispatcher->getDispatchedEvents());
        self::assertSame($event, $dispatched);
        self::assertSame($dispatched, $symfonyDispatcher->getDispatchedEvents()[0]);
    }
}
