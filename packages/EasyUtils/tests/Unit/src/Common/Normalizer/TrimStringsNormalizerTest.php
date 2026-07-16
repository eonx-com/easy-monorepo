<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Tests\Unit\Common\Normalizer;

use EonX\EasyUtils\Common\Normalizer\TrimStringsNormalizer;
use EonX\EasyUtils\Common\Trimmer\RecursiveStringTrimmer;
use EonX\EasyUtils\Common\Trimmer\StringTrimmerInterface;
use EonX\EasyUtils\Tests\Unit\AbstractUnitTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use stdClass;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class TrimStringsNormalizerTest extends AbstractUnitTestCase
{
    /**
     * @see testDenormalizeSucceedsWithTrimValue
     */
    public static function provideDataForDenormalize(): iterable
    {
        yield 'data is string' => [' 123 ', '123'];
        yield 'data is array' => [[' 123 '], ['123']];
    }

    /**
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

    #[DataProvider('provideDataForDenormalize')]
    public function testDenormalizeSucceedsWithTrimValue(mixed $data, mixed $expectedResult): void
    {
        $type = 'no-matter';
        $format = null;
        $context = [];
        $except = ['some-key'];
        $trimmer = new RecursiveStringTrimmer();
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

    #[DataProvider('provideDataForSupportsDenormalization')]
    public function testSupportsDenormalizationReturnsExpectedResult(
        bool $expected,
        mixed $data,
        string $type,
        ?string $format = null,
        ?array $context = null,
    ): void {
        $trimmer = $this->mock(StringTrimmerInterface::class);
        $normalizer = new TrimStringsNormalizer($trimmer);

        $result = $normalizer->supportsDenormalization($data, $type, $format, $context);

        self::assertSame($expected, $result);
    }
}
