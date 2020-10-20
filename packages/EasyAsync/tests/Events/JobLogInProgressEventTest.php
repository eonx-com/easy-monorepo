<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Events;

use EonX\EasyAsync\Data\JobLog;
use EonX\EasyAsync\Data\Target;
use EonX\EasyAsync\Events\JobLogInProgressEvent;
use EonX\EasyAsync\Tests\AbstractTestCase;

/**
 * @coversNothing
 */
final class JobLogInProgressEventTest extends AbstractTestCase
{
    public function testGetJobLog(): void
    {
        $jobLog = new JobLog(new Target('id', 'type'), 'test', 'jobId');
        $event = new JobLogInProgressEvent($jobLog);

        self::assertEquals(\spl_object_hash($jobLog), \spl_object_hash($event->getJobLog()));
    }
}
