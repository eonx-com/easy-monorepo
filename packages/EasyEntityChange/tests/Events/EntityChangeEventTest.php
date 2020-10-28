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
final class EntityChangeEventTest extends AbstractTestCase
{
    public function testEventCreationAndGetters(): void
    {
        $updatedEntity = new UpdatedEntity([], stdClass::class, ['id']);

        $event = new EntityChangeEvent([$updatedEntity]);

        self::assertEquals([$updatedEntity], $event->getChanges());
    }
}
