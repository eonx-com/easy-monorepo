<?php
declare(strict_types=1);

namespace EonX\EasyEventDispatcher\Tests\Unit\Laravel;

use EonX\EasyEventDispatcher\Dispatcher\EventDispatcherInterface;
use EonX\EasyEventDispatcher\Tests\Stub\Even\EventStub;
use Illuminate\Contracts\Events\Dispatcher as IlluminateDispatcherContract;

final class EasyEventDispatcherServiceProviderTest extends AbstractLaravelTestCase
{
    public function testDispatchInLaravel(): void
    {
        $app = $this->getApp();
        /** @var \EonX\EasyEventDispatcher\Tests\Stub\Dispatcher\LaravelEventDispatcherStub $symfonyDispatcher */
        $symfonyDispatcher = $app->get(IlluminateDispatcherContract::class);
        $easyDispatcher = $app->get(EventDispatcherInterface::class);
        $event = new EventStub();

        $dispatched = $easyDispatcher->dispatch($event);

        self::assertNotEmpty($symfonyDispatcher->getDispatchedEvents());
        self::assertSame($event, $dispatched);
        self::assertSame($dispatched, $symfonyDispatcher->getDispatchedEvents()[0]);
    }
}
