<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyEntityChange\Tests\Events;

use LoyaltyCorp\EasyEntityChange\Events\EntityChangeEvent;
use LoyaltyCorp\EasyEntityChange\Tests\AbstractTestCase;

/**
 * @covers \LoyaltyCorp\EasyEntityChange\Events\EntityChangeEvent
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
