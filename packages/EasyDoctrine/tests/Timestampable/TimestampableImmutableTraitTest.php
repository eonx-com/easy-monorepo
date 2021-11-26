<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Tests\Timestampable;

use Carbon\CarbonImmutable;
use EonX\EasyDoctrine\Tests\AbstractTestCase;
use EonX\EasyDoctrine\Timestampable\TimestampableImmutableTrait;

/**
 * @covers \EonX\EasyDoctrine\Timestampable\TimestampableImmutableTrait
 */
final class TimestampableImmutableTraitTest extends AbstractTestCase
{
    public function testGetCreatedAtSucceeds(): void
    {
        $now = CarbonImmutable::now();
        CarbonImmutable::setTestNow($now);
        $object = new class() {
            use TimestampableImmutableTrait;
        };
        $object->updateTimestamps();

        $result = $object->getCreatedAt();

        self::assertEquals($now, $result);
    }

    public function testGetUpdatedAtSucceeds(): void
    {
        $now = CarbonImmutable::now();
        CarbonImmutable::setTestNow($now);
        $object = new class() {
            use TimestampableImmutableTrait;
        };
        $object->updateTimestamps();

        $result = $object->getUpdatedAt();

        self::assertEquals($now, $result);
    }

    public function testUpdateTimestampsSucceeds(): void
    {
        CarbonImmutable::setTestNow('2021-11-24');
        $object = new class() {
            use TimestampableImmutableTrait;
        };

        $object->updateTimestamps();

        self::assertEquals(CarbonImmutable::getTestNow(), $object->getCreatedAt());
        self::assertEquals(CarbonImmutable::getTestNow(), $object->getUpdatedAt());
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        CarbonImmutable::setTestNow();
    }
}
