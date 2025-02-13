<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Tests\Unit\Common\Normalizer;

use Carbon\CarbonImmutable;
use EonX\EasyUtils\Common\Normalizer\CarbonImmutableNormalizer;
use EonX\EasyUtils\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

final class CarbonImmutableNormalizerTest extends AbstractUnitTestCase
{
    /**
     * @see testDenormalizeSucceeds
     */
    public static function provideDateTimeFormats(): iterable
    {
        yield 'Y-m-d H:i:s' => ['dateTime' => '2021-12-16 17:00:00'];

        yield 'Y-m-d\TH:i:sP' => ['dateTime' => '2021-12-16T17:00:00+07:00'];
    }

    #[DataProvider('provideDateTimeFormats')]
    public function testDenormalizeSucceeds(string $dateTime): void
    {
        $normalizer = new CarbonImmutableNormalizer(new DateTimeNormalizer());

        $result = $normalizer->denormalize($dateTime, CarbonImmutable::class);

        self::assertEquals(new CarbonImmutable($dateTime), $result);
    }

    public function testHasCacheableSupportsMethodSucceeds(): void
    {
        $normalizer = new CarbonImmutableNormalizer(new DateTimeNormalizer());

        $result = $normalizer->hasCacheableSupportsMethod();

        self::assertTrue($result);
    }
}
