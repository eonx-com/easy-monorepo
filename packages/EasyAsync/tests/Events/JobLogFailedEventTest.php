<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Events;

use EonX\EasyAsync\Data\JobLog;
use EonX\EasyAsync\Data\Target;
use EonX\EasyAsync\Events\JobLogFailedEvent;
use EonX\EasyAsync\Tests\AbstractTestCase;

/**
 * @coversNothing
 */
final class JobLogFailedEventTest extends AbstractTestCase
{
    public function testGetJobLogAndThrowable(): void
    {
        $jobLog = new JobLog(new Target('id', 'type'), 'test', 'jobId');
        $throwable = new \Exception();
        $event = new JobLogFailedEvent($jobLog, $throwable);

        self::assertEquals(\spl_object_hash($jobLog), \spl_object_hash($event->getJobLog()));
        self::assertEquals(\spl_object_hash($throwable), \spl_object_hash($event->getThrowable()));
    }
}
