<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\Tests;

use EonX\EasyRandom\Bridge\Symfony\Generators\SymfonyUuidV6Generator;
use EonX\EasyRandom\Generators\RandomGenerator;
use EonX\EasyRequestId\UuidFallbackResolver;
use Symfony\Component\Uid\Uuid;

final class UuidFallbackResolverTest extends AbstractTestCase
{
    public function testFallbackCorrelationIdSucceeds(): void
    {
        $sut = new UuidFallbackResolver(
            new RandomGenerator(new SymfonyUuidV6Generator())
        );

        $result = $sut->fallbackCorrelationId();

        self::assertTrue(Uuid::isValid($result));
    }

    public function testFallbackRequestIdSucceeds(): void
    {
        $sut = new UuidFallbackResolver(
            new RandomGenerator(new SymfonyUuidV6Generator())
        );

        $result = $sut->fallbackRequestId();

        self::assertTrue(Uuid::isValid($result));
    }
}
