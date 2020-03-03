<?php
declare(strict_types=1);

namespace EonX\EasyEntityChange\Tests\Events;

use EonX\EasyEntityChange\DataTransferObjects\UpdatedEntity;
use EonX\EasyEntityChange\Events\EntityChangeEvent;
use EonX\EasyEntityChange\Tests\AbstractTestCase;
use stdClass;

/**
 * @covers \EonX\EasyEntityChange\Events\EntityChangeEvent
 */
class EntityChangeEventTest extends AbstractTestCase
{
    /**
     * Tests that DTO's constructor and getters are :chefs-kiss:
     *
     * @return void
     */
    public function testEventCreationAndGetters(): void
    {
        $updatedEntity = new UpdatedEntity(
            [],
            stdClass::class,
            ['id']
        );

        $event = new EntityChangeEvent([$updatedEntity]);

        self::assertSame([$updatedEntity], $event->getChanges());
    }
}
