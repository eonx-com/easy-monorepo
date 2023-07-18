<?php

declare(strict_types=1);

namespace EonX\EasyRequestId\Tests;

use EonX\EasyRandom\Generators\RandomGenerator;
use EonX\EasyRandom\Generators\RandomIntegerGenerator;
use EonX\EasyRandom\Generators\RandomStringGenerator;
use EonX\EasyRandom\Generators\SymfonyUuidV6Generator;
use EonX\EasyRandom\Interfaces\RandomGeneratorInterface;
use EonX\EasyRequestId\UuidFallbackResolver;
use Symfony\Component\Uid\Uuid;

final class UuidFallbackResolverTest extends AbstractTestCase
{
    public function testFallbackCorrelationIdSucceeds(): void
    {
        $randomGenerator = $this->arrangeRandomGenerator();
        $sut = new UuidFallbackResolver($randomGenerator);

        $result = $sut->fallbackCorrelationId();

        self::assertTrue(Uuid::isValid($result));
    }

    public function testFallbackRequestIdSucceeds(): void
    {
        $randomGenerator = $this->arrangeRandomGenerator();
        $sut = new UuidFallbackResolver($randomGenerator);

        $result = $sut->fallbackRequestId();

        self::assertTrue(Uuid::isValid($result));
    }

    private function arrangeRandomGenerator(): RandomGeneratorInterface
    {
        return new RandomGenerator(
            randomStringGenerator: new RandomStringGenerator(),
            randomIntegerGenerator: new RandomIntegerGenerator(),
            uuidGenerator: new SymfonyUuidV6Generator(),
        );
    }
}
