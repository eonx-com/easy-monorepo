<?php
declare(strict_types=1);

namespace Tests\App\Unit\Services\EntityChange\Events;

use LoyaltyCorp\EasyEntityChange\Events\EntityDeleteDataEvent;
use LoyaltyCorp\EasyEntityChange\Tests\AbstractTestCase;

/**
 * @covers \LoyaltyCorp\EasyEntityChange\Events\EntityDeleteDataEvent
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
