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
    /**
     * Updater should set status to completed and finishedAt to now.
     *
     * @return void
     *
     * @throws \EonX\EasyAsync\Exceptions\UnableToGenerateDateTimeException
     */
    public function testCompleted(): void
    {
        $jobLog = $this->getJobLog();
        $updater = $this->getJobLogUpdater();

        $updater->completed($jobLog);

        self::assertEquals(JobLogInterface::STATUS_COMPLETED, $jobLog->getStatus());
        self::assertInstanceOf(\DateTime::class, $jobLog->getFinishedAt());
    }

    /**
     * Updater should set status to failed, finishedAt to now and debug info to array with exception.
     *
     * @return void
     *
     * @throws \EonX\EasyAsync\Exceptions\UnableToGenerateDateTimeException
     */
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

    /**
     * Updater should set status to completed and finishedAt to now.
     *
     * @return void
     *
     * @throws \EonX\EasyAsync\Exceptions\UnableToGenerateDateTimeException
     */
    public function testInProgress(): void
    {
        $jobLog = $this->getJobLog();
        $updater = $this->getJobLogUpdater();

        $updater->inProgress($jobLog);

        self::assertEquals(JobLogInterface::STATUS_IN_PROGRESS, $jobLog->getStatus());
        self::assertInstanceOf(\DateTime::class, $jobLog->getStartedAt());
    }

    /**
     * Get new job log.
     *
     * @return \EonX\EasyAsync\Interfaces\JobLogInterface
     */
    private function getJobLog(): JobLogInterface
    {
        return new JobLog(new Target('id', 'type'), 'test', 'jobId');
    }

    /**
     * Get job log updater.
     *
     * @return \EonX\EasyAsync\Updaters\JobLogUpdater
     */
    private function getJobLogUpdater(): JobLogUpdater
    {
        return new JobLogUpdater(new DateTimeGenerator());
    }
}
