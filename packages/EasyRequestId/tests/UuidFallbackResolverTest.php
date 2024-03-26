<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\Tests;

use EonX\EasyRandom\Generators\RandomGenerator;
use EonX\EasyRandom\Generators\UuidGenerator;
use EonX\EasyRequestId\UuidFallbackResolver;
use Symfony\Component\Uid\Factory\UuidFactory;
use Symfony\Component\Uid\Uuid;

final class UuidFallbackResolverTest extends AbstractTestCase
{
    public function testFallbackCorrelationIdSucceeds(): void
    {
        $sut = new UuidFallbackResolver(
            new RandomGenerator(new UuidGenerator(new UuidFactory()))
        );

        $result = $sut->fallbackCorrelationId();

        self::assertTrue(Uuid::isValid($result));
    }

    public function testFallbackRequestIdSucceeds(): void
    {
        $sut = new UuidFallbackResolver(
            new RandomGenerator(new UuidGenerator(new UuidFactory()))
        );

        $result = $sut->fallbackRequestId();

        self::assertTrue(Uuid::isValid($result));
    }
}
