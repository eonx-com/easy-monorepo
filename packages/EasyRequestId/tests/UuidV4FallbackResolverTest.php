<?php

declare(strict_types=1);

namespace EonX\EasyRequestId\Tests;

use EonX\EasyRandom\RandomGenerator;
use EonX\EasyRandom\UuidV4\RamseyUuidV4Generator;
use EonX\EasyRequestId\UuidV4FallbackResolver;
use Ramsey\Uuid\Uuid;

final class UuidV4FallbackResolverTest extends AbstractTestCase
{
    public function testFallback(): void
    {
        $random = (new RandomGenerator())->setUuidV4Generator(new RamseyUuidV4Generator());
        $fallback = new UuidV4FallbackResolver($random);

        self::assertTrue(Uuid::isValid($fallback->fallbackCorrelationId()));
        self::assertTrue(Uuid::isValid($fallback->fallbackRequestId()));
    }
}
