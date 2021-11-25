<?php

declare(strict_types=1);

namespace EonX\EasyCore\Tests\Doctrine\Timestampable;

use Carbon\CarbonImmutable;
use DateTime;
use DateTimeImmutable;
use EonX\EasyCore\Doctrine\Timestampable\TimestampableImmutableTrait;
use EonX\EasyCore\Tests\AbstractTestCase;

/**
 * @covers \EonX\EasyCore\Doctrine\Timestampable\TimestampableImmutableTrait
 */
final class TimestampableImmutableTraitTest extends AbstractTestCase
{
    public function testGetCreatedAtSucceeds(): void
    {
        $object = new class() {
            use TimestampableImmutableTrait;
        };
        $createdAt = new DateTime();
        $object->setCreatedAt($createdAt);

        $result = $object->getCreatedAt();

        self::assertSame($createdAt, $result);
    }

    public function testGetUpdatedAtSucceeds(): void
    {
        $object = new class() {
            use TimestampableImmutableTrait;
        };
        $updatedAt = new DateTime();
        $object->setUpdatedAt($updatedAt);

        $result = $object->getUpdatedAt();

        self::assertSame($updatedAt, $result);
    }

    public function testSetCreatedAtSucceeds(): void
    {
        $object = new class() {
            use TimestampableImmutableTrait;
        };
        $createdAt = new DateTime();

        $object->setCreatedAt($createdAt);

        self::assertSame($createdAt, $object->getCreatedAt());
    }

    public function testSetUpdatedAtSucceeds(): void
    {
        $object = new class() {
            use TimestampableImmutableTrait;
        };
        $updatedAt = new DateTime();

        $object->setUpdatedAt($updatedAt);

        self::assertSame($updatedAt, $object->getUpdatedAt());
    }

    public function testUpdateTimestampsSucceedsWhenCreatedAtIsImmutable(): void
    {
        CarbonImmutable::setTestNow('2021-11-24');
        $object = new class() {
            use TimestampableImmutableTrait;
        };
        $createdAt = new DateTimeImmutable('2021-11-23');
        $object->setCreatedAt($createdAt);

        $object->updateTimestamps();

        self::assertInstanceOf(DateTimeImmutable::class, $object->getCreatedAt());
        self::assertInstanceOf(DateTimeImmutable::class, $object->getUpdatedAt());
        self::assertEquals($createdAt, $object->getCreatedAt());
        self::assertEquals(CarbonImmutable::getTestNow(), $object->getUpdatedAt());
    }

    public function testUpdateTimestampsSucceedsWhenCreatedAtIsMutable(): void
    {
        CarbonImmutable::setTestNow('2021-11-24');
        $object = new class() {
            use TimestampableImmutableTrait;
        };
        $createdAt = new DateTime('2021-11-23');
        $object->setCreatedAt($createdAt);

        $object->updateTimestamps();

        self::assertInstanceOf(DateTimeImmutable::class, $object->getCreatedAt());
        self::assertInstanceOf(DateTimeImmutable::class, $object->getUpdatedAt());
        self::assertEquals($createdAt, $object->getCreatedAt());
        self::assertEquals(CarbonImmutable::getTestNow(), $object->getUpdatedAt());
    }

    public function testUpdateTimestampsSucceedsWhenCreatedAtIsNotSet(): void
    {
        CarbonImmutable::setTestNow('2021-11-24');
        $object = new class() {
            use TimestampableImmutableTrait;
        };

        $object->updateTimestamps();

        self::assertInstanceOf(DateTimeImmutable::class, $object->getCreatedAt());
        self::assertInstanceOf(DateTimeImmutable::class, $object->getUpdatedAt());
        self::assertEquals(CarbonImmutable::getTestNow(), $object->getCreatedAt());
        self::assertEquals(CarbonImmutable::getTestNow(), $object->getUpdatedAt());
    }
}
