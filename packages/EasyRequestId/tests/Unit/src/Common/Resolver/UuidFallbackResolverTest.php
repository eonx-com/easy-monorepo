<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\Tests\Unit\Common\Resolver;

use EonX\EasyRandom\Generator\RandomGenerator;
use EonX\EasyRandom\Generator\RandomIntegerGenerator;
use EonX\EasyRandom\Generator\RandomStringGenerator;
use EonX\EasyRandom\Generator\UuidGenerator;
use EonX\EasyRequestId\Common\Resolver\UuidFallbackResolver;
use EonX\EasyRequestId\Tests\Unit\AbstractUnitTestCase;
use Symfony\Component\Uid\Factory\UuidFactory;
use Symfony\Component\Uid\Uuid;

final class UuidFallbackResolverTest extends AbstractUnitTestCase
{
    public function testFallbackCorrelationIdSucceeds(): void
    {
        $sut = new UuidFallbackResolver(
            new RandomGenerator(
                new UuidGenerator(new UuidFactory()),
                new RandomIntegerGenerator(),
                new RandomStringGenerator()
            )
        );

        $result = $sut->fallbackCorrelationId();

        self::assertTrue(Uuid::isValid($result));
    }

    public function testFallbackRequestIdSucceeds(): void
    {
        $sut = new UuidFallbackResolver(
            new RandomGenerator(
                new UuidGenerator(new UuidFactory()),
                new RandomIntegerGenerator(),
                new RandomStringGenerator()
            )
        );

        $result = $sut->fallbackRequestId();

        self::assertTrue(Uuid::isValid($result));
    }
}
