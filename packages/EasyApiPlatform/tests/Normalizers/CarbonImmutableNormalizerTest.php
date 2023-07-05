<?php

declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests\Normalizers;

use Carbon\CarbonImmutable;
use EonX\EasyApiPlatform\Normalizers\CarbonImmutableNormalizer;
use EonX\EasyCore\Tests\Bridge\Symfony\AbstractSymfonyTestCase;

/**
 * @covers \EonX\EasyApiPlatform\Normalizers\CarbonImmutableNormalizer
 */
final class CarbonImmutableNormalizerTest extends AbstractSymfonyTestCase
{
    public function testDenormalizeSucceeds(): void
    {
        $data = '2021-12-16T17:00:00+07:00';
        $normalizer = new CarbonImmutableNormalizer();

        $result = $normalizer->denormalize($data, 'some-json');

        self::assertEquals(new CarbonImmutable($data), $result);
    }

    public function testHasCacheableSupportsMethodSucceeds(): void
    {
        $normalizer = new CarbonImmutableNormalizer();

        $result = $normalizer->hasCacheableSupportsMethod();

        self::assertTrue($result);
    }
}
