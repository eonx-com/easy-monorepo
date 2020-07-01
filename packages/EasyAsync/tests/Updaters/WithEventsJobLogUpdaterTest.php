<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Updaters;

use EonX\EasyAsync\Data\JobLog;
use EonX\EasyAsync\Data\Target;
use EonX\EasyAsync\Events\JobLogCompletedEvent;
use EonX\EasyAsync\Events\JobLogFailedEvent;
use EonX\EasyAsync\Events\JobLogInProgressEvent;
use EonX\EasyAsync\Generators\DateTimeGenerator;
use EonX\EasyAsync\Interfaces\EventDispatcherInterface;
use EonX\EasyAsync\Interfaces\JobLogInterface;
use EonX\EasyAsync\Tests\AbstractTestCase;
use EonX\EasyAsync\Tests\Stubs\EventDispatcherStub;
use EonX\EasyAsync\Updaters\JobLogUpdater;
use EonX\EasyAsync\Updaters\WithEventsJobLogUpdater;

final class WithEventsJobLogUpdaterTest extends AbstractTestCase
{
    public function testCompleted(): void
    {
        $jobLog = $this->getJobLog();
        $dispatcher = new EventDispatcherStub();
        $updater = $this->getJobLogUpdater($dispatcher);

        $updater->completed($jobLog);

        self::assertEquals(JobLogInterface::STATUS_COMPLETED, $jobLog->getStatus());
        self::assertInstanceOf(\DateTime::class, $jobLog->getFinishedAt());
        self::assertCount(1, $dispatcher->getDispatchedEvents());
        self::assertInstanceOf(JobLogCompletedEvent::class, $dispatcher->getDispatchedEvents()[0]);
    }

    public function testFailed(): void
    {
        $jobLog = $this->getJobLog();
        $dispatcher = new EventDispatcherStub();
        $updater = $this->getJobLogUpdater($dispatcher);
        $throwable = new \Exception('test-message');

        $debugInfo = [
            'exception' => [
                'message' => 'test-message',
                'class' => \get_class($throwable),
                'code' => $throwable->getCode(),
                'file' => $throwable->getFile(),
                'line' => $throwable->getLine(),
                'trace' => $throwable->getTraceAsString(),
            ],
        ];

        $updater->failed($jobLog, $throwable);

        self::assertEquals(JobLogInterface::STATUS_FAILED, $jobLog->getStatus());
        self::assertInstanceOf(\DateTime::class, $jobLog->getFinishedAt());
        self::assertEquals($debugInfo, $jobLog->getDebugInfo());
        self::assertCount(1, $dispatcher->getDispatchedEvents());
        self::assertInstanceOf(JobLogFailedEvent::class, $dispatcher->getDispatchedEvents()[0]);
    }

    public function testInProgress(): void
    {
        $jobLog = $this->getJobLog();
        $dispatcher = new EventDispatcherStub();
        $updater = $this->getJobLogUpdater($dispatcher);

        $updater->inProgress($jobLog);

        self::assertEquals(JobLogInterface::STATUS_IN_PROGRESS, $jobLog->getStatus());
        self::assertInstanceOf(\DateTime::class, $jobLog->getStartedAt());
        self::assertCount(1, $dispatcher->getDispatchedEvents());
        self::assertInstanceOf(JobLogInProgressEvent::class, $dispatcher->getDispatchedEvents()[0]);
    }

    private function getJobLog(): JobLogInterface
    {
        return new JobLog(new Target('id', 'type'), 'test', 'jobId');
    }

    private function getJobLogUpdater(EventDispatcherInterface $dispatcher): WithEventsJobLogUpdater
    {
        return new WithEventsJobLogUpdater($dispatcher, new JobLogUpdater(new DateTimeGenerator()));
    }
}
