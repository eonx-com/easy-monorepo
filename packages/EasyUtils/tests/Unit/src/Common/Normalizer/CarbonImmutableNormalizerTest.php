<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Tests\Unit\Common\Normalizer;

use Carbon\CarbonImmutable;
use EonX\EasyUtils\Common\Normalizer\CarbonImmutableNormalizer;
use EonX\EasyUtils\Tests\Unit\AbstractUnitTestCase;

final class CarbonImmutableNormalizerTest extends AbstractUnitTestCase
{
    public function testDenormalizeSucceeds(): void
    {
        $data = '2021-12-16T17:00:00+07:00';
        $normalizer = new CarbonImmutableNormalizer();

        $result = $normalizer->denormalize($data, CarbonImmutable::class);

        self::assertEquals(new CarbonImmutable($data), $result);
    }

    public function testHasCacheableSupportsMethodSucceeds(): void
    {
        $normalizer = new CarbonImmutableNormalizer();

        $result = $normalizer->hasCacheableSupportsMethod();

        self::assertTrue($result);
    }
}
