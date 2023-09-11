<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Tests\Events;

use EonX\EasyDoctrine\Events\EntityCreatedEvent;
use EonX\EasyDoctrine\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use stdClass;

#[CoversClass(EntityCreatedEvent::class)]
final class EntityCreatedEventTest extends AbstractTestCase
{
    public function testGetChangeSetSucceeds(): void
    {
        /** @var object $expectedEntity */
        $expectedEntity = $this->prophesize(stdClass::class)->reveal();
        $event = new EntityCreatedEvent($expectedEntity, ['changedField' => '1']);

        $changeSet = $event->getChangeSet();

        self::assertSame(['changedField' => '1'], $changeSet);
    }

    public function testGetEntitySucceeds(): void
    {
        /** @var object $expectedEntity */
        $expectedEntity = $this->prophesize(stdClass::class)->reveal();
        $event = new EntityCreatedEvent($expectedEntity, ['changedField' => '1']);

        $actualEntity = $event->getEntity();

        self::assertSame($expectedEntity, $actualEntity);
    }
}
