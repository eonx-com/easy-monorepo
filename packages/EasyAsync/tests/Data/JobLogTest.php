<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Data;

use EonX\EasyAsync\Data\JobLog;
use EonX\EasyAsync\Tests\AbstractTestCase;

final class JobLogTest extends AbstractTestCase
{
    /**
     * Test job log setters and getters.
     *
     * @return void
     *
     * @throws \Nette\Utils\JsonException
     */
    public function testJobLog(): void
    {
        $expected = [
            'target_id' => 'id',
            'target_type' => 'type',
            'id' => 'id',
            'job_id' => 'jobId',
            'type' => 'test',
            'status' => JobLog::STATUS_IN_PROGRESS,
            'finished_at' => null,
            'started_at' => null,
            'debug_info' => null,
            'failure_params' => null,
            'failure_reason' => 'failure_reason',
            'validation_errors' => null
        ];

        $jobLog = JobLog::fromArray($expected);

        self::assertEquals($expected, $jobLog->toArray());

        $jobLog->addDebugInfo('exception', ['class' => 'exception']);

        self::assertEquals(['exception' => ['class' => 'exception']], $jobLog->getDebugInfo());
    }
}
