<?php
declare(strict_types=1);

namespace Tests\App\Unit\Services\EntityChange\Events;

use EonX\EasyEntityChange\Events\EntityDeleteDataEvent;
use EonX\EasyEntityChange\Tests\AbstractTestCase;

/**
 * @covers \EonX\EasyEntityChange\Events\EntityDeleteDataEvent
 */
class EntityDeleteDataEventTest extends AbstractTestCase
{
    /**
     * Tests event.
     *
     * @return void
     */
    public function testEvent(): void
    {
        $class = new \stdClass();
        $event = new EntityDeleteDataEvent([$class]);

        static::assertSame([$class], $event->getDeletes());
    }
}
