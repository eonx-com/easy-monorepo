<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Tests\Events;

use EonX\EasyDoctrine\Events\DeferredEntityCreatedEvent;
use EonX\EasyDoctrine\Tests\AbstractTestCase;
use stdClass;

/**
 * @covers \EonX\EasyDoctrine\Events\DeferredEntityCreatedEvent
 */
final class DeferredEntityCreatedEventTest extends AbstractTestCase
{
    public function testGetChangeSetSucceeds(): void
    {
        /** @var object $expectedEntity */
        $expectedEntity = $this->prophesize(stdClass::class)->reveal();
        $event = new DeferredEntityCreatedEvent($expectedEntity, ['changedField' => '1']);

        $changeSet = $event->getChangeSet();

        self::assertSame(['changedField' => '1'], $changeSet);
    }

    public function testGetEntitySucceeds(): void
    {
        /** @var object $expectedEntity */
        $expectedEntity = $this->prophesize(stdClass::class)->reveal();
        $event = new DeferredEntityCreatedEvent($expectedEntity, ['changedField' => '1']);

        $actualEntity = $event->getEntity();

        self::assertSame($expectedEntity, $actualEntity);
    }
}
