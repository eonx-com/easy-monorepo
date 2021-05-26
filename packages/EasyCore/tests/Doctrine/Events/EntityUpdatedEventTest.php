<?php

declare(strict_types=1);

namespace EonX\EasyCore\Tests\Doctrine\Events;

use EonX\EasyCore\Doctrine\Events\EntityUpdatedEvent;
use EonX\EasyCore\Tests\AbstractTestCase;
use stdClass;

/**
 * @covers \EonX\EasyCore\Doctrine\Events\EntityUpdatedEvent
 */
final class EntityUpdatedEventTest extends AbstractTestCase
{
    public function testGetEntitySucceeds(): void
    {
        /** @var object $expectedEntity */
        $expectedEntity = $this->prophesize(stdClass::class)->reveal();
        $event = new EntityUpdatedEvent($expectedEntity);

        $actualEntity = $event->getEntity();

        self::assertSame($expectedEntity, $actualEntity);
    }
}
