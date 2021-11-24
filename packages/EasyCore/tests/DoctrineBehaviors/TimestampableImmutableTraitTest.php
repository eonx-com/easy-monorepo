<?php

declare(strict_types=1);

namespace EonX\EasyCore\Tests\DoctrineBehaviors;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use EonX\EasyCore\DoctrineBehaviors\TimestampableImmutableTrait;
use EonX\EasyCore\Tests\AbstractTestCase;

/**
 * @covers \EonX\EasyCore\DoctrineBehaviors\TimestampableImmutableTrait
 */
final class TimestampableImmutableTraitTest extends AbstractTestCase
{
    /**
     * @return iterable<array>
     *
     * @see testUpdateTimestampsSucceeds
     */
    public function provideCreatedAt(): iterable
    {
        yield 'Mutable date' => [new DateTime('2021-11-12 01:10:22')];

        yield 'Immutable date' => [new DateTimeImmutable('2021-11-12 01:10:22')];
    }

    /**
     * @dataProvider provideCreatedAt
     */
    public function testUpdateTimestampsSucceeds(DateTimeInterface $createdAt): void
    {
        $class = new class() {
            use TimestampableImmutableTrait;
        };
        $class->setCreatedAt(clone $createdAt);

        $class->updateTimestamps();

        $format = 'Y-m-d H:s:i';
        self::assertInstanceOf(DateTimeImmutable::class, $class->getCreatedAt());
        self::assertInstanceOf(DateTimeImmutable::class, $class->getUpdatedAt());
        self::assertSame($createdAt->format($format), $class->getCreatedAt()->format($format));
    }
}
