<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Bridge\Laravel\Events;

use EonX\EasyAsync\Bridge\Laravel\Events\EventDispatcher;
use EonX\EasyAsync\Data\Job;
use EonX\EasyAsync\Data\Target;
use EonX\EasyAsync\Events\JobCompletedEvent;
use EonX\EasyAsync\Tests\AbstractTestCase;
use Illuminate\Contracts\Events\Dispatcher;
use Mockery\MockInterface;

final class EventDispatcherTest extends AbstractTestCase
{
    /**
     * EventDispatcher should dispatch given event and return it.
     *
     * @return void
     */
    public function testDispatch(): void
    {
        $event = new JobCompletedEvent(new Job(new Target('id', 'type'), 'test'));

        /** @var \Illuminate\Contracts\Events\Dispatcher $illuminateDispatcher */
        $illuminateDispatcher = $this->mock(
            Dispatcher::class,
            static function (MockInterface $mock) use ($event): void {
                $mock
                    ->shouldReceive('dispatch')
                    ->once()
                    ->with($event)
                    ->andReturn($event);
            });

        $dispatcher = new EventDispatcher($illuminateDispatcher);

        self::assertEquals(\spl_object_hash($event), \spl_object_hash($dispatcher->dispatch($event)));
    }
}
