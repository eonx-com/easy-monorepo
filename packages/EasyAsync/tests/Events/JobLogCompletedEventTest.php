<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Events;

use EonX\EasyAsync\Data\JobLog;
use EonX\EasyAsync\Data\Target;
use EonX\EasyAsync\Events\JobLogCompletedEvent;
use EonX\EasyAsync\Tests\AbstractTestCase;

final class JobLogCompletedEventTest extends AbstractTestCase
{
    /**
     * Event should return given job log.
     *
     * @return void
     */
    public function testGetJobLog(): void
    {
        $jobLog = new JobLog(new Target('id', 'type'), 'test', 'jobId');
        $event = new JobLogCompletedEvent($jobLog);

        self::assertEquals(\spl_object_hash($jobLog), \spl_object_hash($event->getJobLog()));
    }
}
