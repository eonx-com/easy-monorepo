<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Updaters;

use EonX\EasyAsync\Data\JobLog;
use EonX\EasyAsync\Data\Target;
use EonX\EasyAsync\Generators\DateTimeGenerator;
use EonX\EasyAsync\Interfaces\JobLogInterface;
use EonX\EasyAsync\Tests\AbstractTestCase;
use EonX\EasyAsync\Updaters\JobLogUpdater;

final class JobLogUpdaterTest extends AbstractTestCase
{
    public function testCompleted(): void
    {
        $jobLog = $this->getJobLog();
        $updater = $this->getJobLogUpdater();

        $updater->completed($jobLog);

        self::assertEquals(JobLogInterface::STATUS_COMPLETED, $jobLog->getStatus());
        self::assertInstanceOf(\DateTime::class, $jobLog->getFinishedAt());
    }

    public function testFailed(): void
    {
        $jobLog = $this->getJobLog();
        $updater = $this->getJobLogUpdater();
        $throwable = new \Exception();

        $debugInfo = [
            'exception' => [
                'class' => \get_class($throwable),
                'code' => $throwable->getCode(),
                'file' => $throwable->getFile(),
                'line' => $throwable->getLine(),
                'trace' => $throwable->getTraceAsString()
            ]
        ];

        $updater->failed($jobLog, $throwable);

        self::assertEquals(JobLogInterface::STATUS_FAILED, $jobLog->getStatus());
        self::assertInstanceOf(\DateTime::class, $jobLog->getFinishedAt());
        self::assertEquals($debugInfo, $jobLog->getDebugInfo());
    }

    public function testInProgress(): void
    {
        $jobLog = $this->getJobLog();
        $updater = $this->getJobLogUpdater();

        $updater->inProgress($jobLog);

        self::assertEquals(JobLogInterface::STATUS_IN_PROGRESS, $jobLog->getStatus());
        self::assertInstanceOf(\DateTime::class, $jobLog->getStartedAt());
    }

    private function getJobLog(): JobLogInterface
    {
        return new JobLog(new Target('id', 'type'), 'test', 'jobId');
    }

    private function getJobLogUpdater(): JobLogUpdater
    {
        return new JobLogUpdater(new DateTimeGenerator());
    }
}
