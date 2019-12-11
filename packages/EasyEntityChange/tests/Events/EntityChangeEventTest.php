<?php
declare(strict_types=1);

namespace EonX\EasyEntityChange\Tests\Events;

use EonX\EasyEntityChange\Events\EntityChangeEvent;
use EonX\EasyEntityChange\Tests\AbstractTestCase;

/**
 * @covers \EonX\EasyEntityChange\Events\EntityChangeEvent
 */
class EntityChangeEventTest extends AbstractTestCase
{
    /**
     * Tests event.
     *
     * @return void
     */
    public function testEvent(): void
    {
        $event = new EntityChangeEvent(['delete' => ['id']], ['class' => ['hash' => 'id']]);

        static::assertSame(['delete' => ['id']], $event->getDeletes());
        static::assertSame(['class' => ['hash' => 'id']], $event->getUpdates());
    }
}
