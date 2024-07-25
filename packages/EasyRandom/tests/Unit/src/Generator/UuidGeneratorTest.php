<?php
declare(strict_types=1);

namespace EonX\EasyRandom\Tests\Unit\Generator;

use EonX\EasyRandom\Tests\Fixture\App\Provider\UuidProvider;
use EonX\EasyRandom\Tests\Unit\AbstractUnitTestCase;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV7;

final class UuidGeneratorTest extends AbstractUnitTestCase
{
    public function testItSucceeds(): void
    {
        $uuidProvider = self::getService(UuidProvider::class);

        $result = $uuidProvider->provide();

        self::assertInstanceOf(UuidV7::class, Uuid::fromString($result));
    }
}
