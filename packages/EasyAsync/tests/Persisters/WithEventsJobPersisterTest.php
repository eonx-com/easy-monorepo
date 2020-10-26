<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Persisters;

use EonX\EasyAsync\Data\Job;
use EonX\EasyAsync\Data\Target;
use EonX\EasyAsync\Events\JobCompletedEvent;
use EonX\EasyAsync\Interfaces\JobInterface;
use EonX\EasyAsync\Persisters\WithEventsJobPersister;
use EonX\EasyAsync\Tests\AbstractTestCase;
use EonX\EasyAsync\Tests\Stubs\EventDispatcherStub;
use EonX\EasyAsync\Tests\Stubs\JobPersisterStub;
use EonX\EasyPagination\Data\StartSizeData;

final class WithEventsJobPersisterTest extends AbstractTestCase
{
    /**
     * @return iterable<mixed>
     *
     * @see testMethods
     */
    public function providerTestMethods(): iterable
    {
        yield 'find' => [
            static function (WithEventsJobPersister $persister): void {
                $persister->find('jobId');
            },
            'find',
        ];

        yield 'findForTarget' => [
            static function (WithEventsJobPersister $persister): void {
                $persister->findForTarget(new Target('id', 'type'), new StartSizeData(1, 15));
            },
            'findForTarget',
        ];

        yield 'findForTargetType' => [
            static function (WithEventsJobPersister $persister): void {
                $persister->findForTargetType(new Target('id', 'type'), new StartSizeData(1, 15));
            },
            'findForTargetType',
        ];

        yield 'findForType' => [
            static function (WithEventsJobPersister $persister): void {
                $persister->findForType('type', new StartSizeData(1, 15));
            },
            'findForType',
        ];

        yield 'findOneForUpdate' => [
            static function (WithEventsJobPersister $persister): void {
                $persister->findOneForUpdate('jobId');
            },
            'findOneForUpdate',
        ];

        yield 'remove' => [
            static function (WithEventsJobPersister $persister): void {
                $persister->remove(new Job(new Target('id', 'type'), 'test'));
            },
            'remove',
        ];

        yield 'persist no event' => [
            static function (WithEventsJobPersister $persister): void {
                $persister->persist(new Job(new Target('id', 'type'), 'test'));
            },
            'persist',
        ];

        yield 'persist with event' => [
            static function (WithEventsJobPersister $persister): void {
                $job = new Job(new Target('id', 'type'), 'test');
                $job->setStatus(JobInterface::STATUS_COMPLETED);

                $persister->persist($job);
            },
            'persist',
            JobCompletedEvent::class,
        ];
    }

    /**
     * @dataProvider providerTestMethods
     */
    public function testMethods(callable $call, string $method, ?string $eventClass = null): void
    {
        $dispatcher = new EventDispatcherStub();
        $persister = (new JobPersisterStub())->setForSingle(new Job(new Target('id', 'type'), 'test'));
        $withEventsJobPersister = new WithEventsJobPersister($persister, $dispatcher);

        $countEvents = $eventClass !== null ? 1 : 0;

        \call_user_func($call, $withEventsJobPersister);

        self::assertCount($countEvents, $dispatcher->getDispatchedEvents());
        self::assertCount(1, $persister->getMethodCalls());
        self::assertEquals($method, $persister->getMethodCalls()[0]);

        if ($eventClass !== null) {
            self::assertInstanceOf($eventClass, $dispatcher->getDispatchedEvents()[0]);
        }
    }
}
