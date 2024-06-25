<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Tests\Unit\Common\Entity;

use Carbon\CarbonImmutable;
use EonX\EasyDoctrine\Common\Entity\TimestampableTrait;
use EonX\EasyDoctrine\Tests\Unit\AbstractUnitTestCase;

final class TimestampableTraitTest extends AbstractUnitTestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        CarbonImmutable::setTestNow();
    }

    public function testGetCreatedAtSucceeds(): void
    {
        $now = CarbonImmutable::now();
        CarbonImmutable::setTestNow($now);
        $object = new class() {
            use TimestampableTrait;
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
            use TimestampableTrait;
        };
        $object->updateTimestamps();

        $result = $object->getUpdatedAt();

        self::assertEquals($now, $result);
    }

    public function testUpdateTimestampsSucceeds(): void
    {
        CarbonImmutable::setTestNow('2021-11-24');
        $object = new class() {
            use TimestampableTrait;
        };

        $object->updateTimestamps();

        self::assertEquals(CarbonImmutable::getTestNow(), $object->getCreatedAt());
        self::assertEquals(CarbonImmutable::getTestNow(), $object->getUpdatedAt());
    }
}
