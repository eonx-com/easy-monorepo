<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Tests\Unit\EntityEvent\Attribute;

use EonX\EasyDoctrine\EntityEvent\Attribute\AsEntityCreatedEventListener;
use EonX\EasyDoctrine\EntityEvent\Attribute\AsEntityDeletedEventListener;
use EonX\EasyDoctrine\EntityEvent\Attribute\AsEntityUpdateEventListener;
use EonX\EasyDoctrine\Tests\Fixture\App\Entity\Product;
use EonX\EasyDoctrine\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(AsEntityCreatedEventListener::class)]
#[CoversClass(AsEntityDeletedEventListener::class)]
#[CoversClass(AsEntityUpdateEventListener::class)]
final class AsEntityEventListenerTest extends AbstractUnitTestCase
{
    public function testAsEntityCreatedEventListenerBuildEventName(): void
    {
        self::assertSame(
            'entity.created.' . Product::class,
            AsEntityCreatedEventListener::buildEventName(Product::class)
        );
    }

    public function testAsEntityDeletedEventListenerBuildEventName(): void
    {
        self::assertSame(
            'entity.deleted.' . Product::class,
            AsEntityDeletedEventListener::buildEventName(Product::class)
        );
    }

    public function testAsEntityUpdateEventListenerBuildEventName(): void
    {
        self::assertSame(
            'entity.updated.' . Product::class,
            AsEntityUpdateEventListener::buildEventName(Product::class)
        );
    }
}
