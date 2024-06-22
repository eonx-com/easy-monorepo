<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\Tests;

use EonX\EasyRandom\Generator\RandomGenerator;
use EonX\EasyRandom\Generator\SymfonyUuidV6Generator;
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
