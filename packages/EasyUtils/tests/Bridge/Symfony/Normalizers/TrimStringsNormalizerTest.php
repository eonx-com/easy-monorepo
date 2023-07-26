<?php

declare(strict_types=1);

namespace EonX\EasyUtils\Tests\Bridge\Symfony\Normalizers;

use EonX\EasyUtils\Bridge\Symfony\Normalizers\TrimStringsNormalizer;
use EonX\EasyUtils\StringTrimmers\RecursiveStringTrimmer;
use EonX\EasyUtils\StringTrimmers\StringTrimmerInterface;
use EonX\EasyUtils\Tests\AbstractTestCase;
use Mockery\MockInterface;
use stdClass;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class TrimStringsNormalizerTest extends AbstractTestCase
{
    /**
     * @return iterable<mixed>
     *
     * @see testDenormalizeSucceedsWithTrimValue
     */
    public static function provideDataForDenormalize(): iterable
    {
        yield 'data is string' => [' 123 ', '123'];
        yield 'data is array' => [[' 123 '], ['123']];
    }

    /**
     * @return iterable<mixed>
     *
     * @see testSupportsDenormalizationReturnsExpectedResult
     */
    public static function provideDataForSupportsDenormalization(): iterable
    {
        yield 'data is an array' => [
            'expected' => true,
            'data' => [],
            'type' => 'no-matter',
            'format' => 'no-matter',
            'context' => [
                'some-key' => 'some-value',
            ],
        ];
        yield 'data is a string' => [
            'expected' => true,
            'data' => 'some-correct-value',
            'type' => 'no-matter',
            'format' => 'no-matter',
            'context' => [
                'some-key' => 'some-value',
            ],
        ];
        yield 'already called' => [
            'expected' => false,
            'data' => 'some-correct-value',
            'type' => 'no-matter',
            'format' => 'no-matter',
            'context' => [
                'TRIM_STRINGS_ALREADY_CALLED' => true,
            ],
        ];
        yield 'data is an object' => [
            'expected' => false,
            'data' => new stdClass(),
            'type' => 'no-matter',
            'format' => 'no-matter',
            'context' => null,
        ];
        yield 'data is an integer' => [
            'expected' => false,
            'data' => 123,
            'type' => 'no-matter',
            'format' => 'no-matter',
            'context' => null,
        ];
        yield 'data is a float' => [
            'expected' => false,
            'data' => 12.34,
            'type' => 'no-matter',
            'format' => 'no-matter',
            'context' => null,
        ];
        yield 'data is a bool' => [
            'expected' => false,
            'data' => true,
            'type' => 'no-matter',
            'format' => 'no-matter',
            'context' => null,
        ];
    }

    /**
     * @dataProvider provideDataForDenormalize
     */
    public function testDenormalizeSucceedsWithTrimValue(mixed $data, mixed $expectedResult): void
    {
        $type = 'no-matter';
        $format = null;
        $context = [];
        $except = ['some-key'];
        $trimmer = new RecursiveStringTrimmer();
        /** @var \Symfony\Component\Serializer\Normalizer\DenormalizerInterface $innerDenormalizer */
        $innerDenormalizer = $this->mock(
            DenormalizerInterface::class,
            static function (MockInterface $mock) use ($expectedResult, $type, $format, $context): void {
                $mock->shouldReceive('denormalize')
                    ->once()
                    ->with(
                        $expectedResult,
                        $type,
                        $format,
                        \array_merge($context, [
                            'TRIM_STRINGS_ALREADY_CALLED' => true,
                        ])
                    )
                    ->andReturn($expectedResult);
            }
        );
        $normalizer = new TrimStringsNormalizer($trimmer, $except);
        $normalizer->setDenormalizer($innerDenormalizer);

        $result = $normalizer->denormalize($data, $type, $format, $context);

        self::assertSame($expectedResult, $result);
    }

    /**
     * @param mixed[]|null $context
     *
     * @dataProvider provideDataForSupportsDenormalization
     */
    public function testSupportsDenormalizationReturnsExpectedResult(
        bool $expected,
        mixed $data,
        string $type,
        ?string $format = null,
        ?array $context = null,
    ): void {
        /** @var \EonX\EasyUtils\StringTrimmers\StringTrimmerInterface $trimmer */
        $trimmer = $this->mock(StringTrimmerInterface::class);
        $normalizer = new TrimStringsNormalizer($trimmer);

        $result = $normalizer->supportsDenormalization($data, $type, $format, $context);

        self::assertSame($expected, $result);
    }
}
