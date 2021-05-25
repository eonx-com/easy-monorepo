<?php

declare(strict_types=1);

namespace EonX\EasyCore\Tests\Doctrine\Events;

use EonX\EasyCore\Doctrine\Events\EntityCreatedEvent;
use EonX\EasyCore\Tests\AbstractTestCase;
use stdClass;

/**
 * @covers \EonX\EasyCore\Doctrine\Events\EntityCreatedEvent
 */
final class EntityCreatedEventTest extends AbstractTestCase
{
    public function testGetEntitySucceeds(): void
    {
        /** @var object $expectedEntity */
        $expectedEntity = $this->prophesize(stdClass::class)->reveal();
        $event = new EntityCreatedEvent($expectedEntity);

        $actualEntity = $event->getEntity();

        self::assertSame($expectedEntity, $actualEntity);
    }
}
