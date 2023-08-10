<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Tests\Events;

use EonX\EasyDoctrine\Events\EntityUpdatedEvent;
use EonX\EasyDoctrine\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use stdClass;

#[CoversClass(EntityUpdatedEvent::class)]
final class EntityUpdatedEventTest extends AbstractTestCase
{
    public function testGetChangeSetSucceeds(): void
    {
        /** @var object $expectedEntity */
        $expectedEntity = $this->prophesize(stdClass::class)->reveal();
        $event = new EntityUpdatedEvent($expectedEntity, ['changedField' => '1']);

        $changeSet = $event->getChangeSet();

        self::assertSame(['changedField' => '1'], $changeSet);
    }

    public function testGetEntitySucceeds(): void
    {
        /** @var object $expectedEntity */
        $expectedEntity = $this->prophesize(stdClass::class)->reveal();
        $event = new EntityUpdatedEvent($expectedEntity, ['changedField' => '1']);

        $actualEntity = $event->getEntity();

        self::assertSame($expectedEntity, $actualEntity);
    }
}
