<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Bridge\Symfony\Events;

use EonX\EasyAsync\Bridge\Symfony\Events\EventDispatcher;
use EonX\EasyAsync\Data\Job;
use EonX\EasyAsync\Data\Target;
use EonX\EasyAsync\Events\JobCompletedEvent;
use EonX\EasyAsync\Tests\AbstractTestCase;
use Mockery\MockInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

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

        /** @var \Symfony\Contracts\EventDispatcher\EventDispatcherInterface $symfonyDispatcher */
        $symfonyDispatcher = $this->mock(
            EventDispatcherInterface::class,
            static function (MockInterface $mock) use ($event): void {
                $mock
                    ->shouldReceive('dispatch')
                    ->once()
                    ->with($event)
                    ->andReturn($event);
            }
        );

        $dispatcher = new EventDispatcher($symfonyDispatcher);

        self::assertEquals(\spl_object_hash($event), \spl_object_hash($dispatcher->dispatch($event)));
    }
}
