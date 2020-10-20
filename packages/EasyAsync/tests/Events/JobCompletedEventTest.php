<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Events;

use EonX\EasyAsync\Data\Job;
use EonX\EasyAsync\Data\Target;
use EonX\EasyAsync\Events\JobCompletedEvent;
use EonX\EasyAsync\Tests\AbstractTestCase;

/**
 * @coversNothing
 */
final class JobCompletedEventTest extends AbstractTestCase
{
    public function testGetJob(): void
    {
        $job = new Job(new Target('id', 'type'), 'test');
        $event = new JobCompletedEvent($job);

        self::assertEquals(\spl_object_hash($job), \spl_object_hash($event->getJob()));
    }
}
