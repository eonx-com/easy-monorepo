<?php

declare(strict_types=1);

namespace EonX\EasyCore\Tests\Doctrine\Events;

use EonX\EasyCore\Doctrine\Events\EntityUpdatedEvent;
use EonX\EasyCore\Interfaces\DatabaseEntityInterface;
use EonX\EasyCore\Tests\AbstractTestCase;

/**
 * @covers \EonX\EasyCore\Doctrine\Events\EntityUpdatedEvent
 */
final class EntityUpdatedEventTest extends AbstractTestCase
{
    public function testGetEntitySucceeds(): void
    {
        /** @var \EonX\EasyCore\Interfaces\DatabaseEntityInterface $expectedEntity */
        $expectedEntity = $this->prophesize(DatabaseEntityInterface::class)->reveal();
        $event = new EntityUpdatedEvent($expectedEntity);

        $actualEntity = $event->getEntity();

        self::assertSame($expectedEntity, $actualEntity);
    }
}
