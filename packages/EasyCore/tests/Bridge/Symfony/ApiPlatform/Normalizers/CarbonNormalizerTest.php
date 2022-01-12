<?php

declare(strict_types=1);

namespace EonX\EasyCore\Tests\Bridge\Symfony\ApiPlatform\Normalizers;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Carbon\CarbonTimeZone;
use EonX\EasyCore\Bridge\Symfony\ApiPlatform\Normalizers\CarbonNormalizer;
use EonX\EasyCore\Tests\Bridge\Symfony\AbstractSymfonyTestCase;
use stdClass;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;

/**
 * @covers \EonX\EasyCore\Bridge\Symfony\ApiPlatform\Normalizers\CarbonNormalizer
 */
final class CarbonNormalizerTest extends AbstractSymfonyTestCase
{
    /**
     * @var string
     */
    private const FORMAT_KEY = 'datetime_format';

    /**
     * @var string
     */
    private const TIMEZONE_KEY = 'datetime_timezone';

    /**
     * @return iterable<mixed>
     *
     * @see testNormalizeSucceeds
     */
    public function provideDataForNormalize(): iterable
    {
        CarbonImmutable::setTestNow('2021-12-16 10:00:00');
        yield 'without context' => [
            'expectedResult' => '2021-12-16T10:00:00+00:00',
            'carbon' => CarbonImmutable::now(),
        ];

        yield 'with CarbonTimeZone timezone in context' => [
            'expectedResult' => '2021-12-16T17:00:00+07:00',
            'carbon' => CarbonImmutable::now(),
            'context' => [self::TIMEZONE_KEY => CarbonTimeZone::create('Asia/Krasnoyarsk')],
        ];

        yield 'with string timezone in context' => [
            'expectedResult' => '2021-12-16T17:00:00+07:00',
            'carbon' => CarbonImmutable::now(),
            'context' => [self::TIMEZONE_KEY => 'Asia/Krasnoyarsk'],
        ];

        yield 'with format in context' => [
            'expectedResult' => '2021-12-16T10:00:00.000+00:00',
            'carbon' => CarbonImmutable::now(),
            'context' => [self::FORMAT_KEY => CarbonInterface::RFC3339_EXTENDED],
        ];

        yield 'with format and timezone from constructor' => [
            'expectedResult' => '2021-12-16T17:00:00.000+07:00',
            'carbon' => CarbonImmutable::now(),
            'context' => null,
            'format' => CarbonInterface::RFC3339_EXTENDED,
            'timezone' => 'Asia/Krasnoyarsk',
        ];
    }

    public function testHasCacheableSupportsMethodSucceeds(): void
    {
        $normalizer = new CarbonNormalizer();

        $result = $normalizer->hasCacheableSupportsMethod();

        self::assertTrue($result);
    }

    /**
     * @param mixed[]|null $context
     *
     * @dataProvider provideDataForNormalize
     */
    public function testNormalizeSucceeds(
        string $expectedResult,
        CarbonInterface $carbon,
        ?array $context = null,
        ?string $format = null,
        ?string $timezone = null
    ): void {
        $normalizer = new CarbonNormalizer($format, $timezone);

        $result = $normalizer->normalize($carbon, null, $context);

        self::assertSame($expectedResult, $result);
    }

    public function testNormalizeThrowsException(): void
    {
        $normalizer = new CarbonNormalizer();

        $this->safeCall(static function () use ($normalizer) {
            $normalizer->normalize(new stdClass());
        });

        $this->assertThrownException(InvalidArgumentException::class, 0);
    }

    public function testSupportsNormalizationReturnsFalse(): void
    {
        $normalizer = new CarbonNormalizer();

        $result = $normalizer->supportsNormalization(new stdClass());

        self::assertFalse($result);
    }

    public function testSupportsNormalizationReturnsTrue(): void
    {
        $normalizer = new CarbonNormalizer();

        $result = $normalizer->supportsNormalization(new Carbon());

        self::assertTrue($result);
    }
}
