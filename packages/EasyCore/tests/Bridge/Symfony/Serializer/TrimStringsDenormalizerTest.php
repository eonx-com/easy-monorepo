<?php

declare(strict_types=1);

namespace EonX\EasyCore\Tests\Bridge\Symfony\Serializer;

use EonX\EasyCore\Bridge\Symfony\Serializer\TrimStringsDenormalizer;
use EonX\EasyCore\Helpers\RecursiveStringsTrimmer;
use EonX\EasyCore\Helpers\StringsTrimmerInterface;
use EonX\EasyCore\Tests\Bridge\Symfony\AbstractSymfonyTestCase;
use Mockery\MockInterface;
use stdClass;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * @covers \EonX\EasyCore\Bridge\Symfony\Serializer\TrimStringsDenormalizer
 *
 * @internal
 */
final class TrimStringsDenormalizerTest extends AbstractSymfonyTestCase
{
    /**
     * @return mixed[]
     *
     * @see testDenormalizeSucceedsWithTrimValue
     */
    public function provideDataForDenormalize(): array
    {
        return [
            'data is string' => [' 123 ', '123'],
            'data is array' => [[' 123 '], ['123']],
        ];
    }

    /**
     * @return mixed[]
     *
     * @see testSupportsDenormalizationReturnsExpectedResult
     */
    public function provideDataForSupportsDenormalization(): array
    {
        return [
            'data is an array' => [
                'expected' => true,
                'data' => [],
                'type' => 'no-matter',
                'format' => 'no-matter',
                'context' => [
                    'some-key' => 'some-value',
                ],
            ],
            'data is a string' => [
                'expected' => true,
                'data' => 'some-correct-value',
                'type' => 'no-matter',
                'format' => 'no-matter',
                'context' => [
                    'some-key' => 'some-value',
                ],
            ],
            'already called' => [
                'expected' => false,
                'data' => 'some-correct-value',
                'type' => 'no-matter',
                'format' => 'no-matter',
                'context' => [
                    'TRIM_STRINGS_ALREADY_CALLED' => true,
                ],
            ],
            'data is an object' => [
                'expected' => false,
                'data' => new stdClass(),
                'type' => 'no-matter',
                'format' => 'no-matter',
                'context' => null,
            ],
            'data is an integer' => [
                'expected' => false,
                'data' => 123,
                'type' => 'no-matter',
                'format' => 'no-matter',
                'context' => null,
            ],
            'data is a float' => [
                'expected' => false,
                'data' => 12.34,
                'type' => 'no-matter',
                'format' => 'no-matter',
                'context' => null,
            ],
            'data is a bool' => [
                'expected' => false,
                'data' => true,
                'type' => 'no-matter',
                'format' => 'no-matter',
                'context' => null,
            ],
        ];
    }

    /**
     * @param mixed $data
     * @param mixed $expectedResult
     *
     * @dataProvider provideDataForDenormalize
     *
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function testDenormalizeSucceedsWithTrimValue($data, $expectedResult): void
    {
        $type = 'no-matter';
        $format = null;
        $context = [];
        $except = ['some-key'];
        $trimmer = new RecursiveStringsTrimmer();
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
        $denormalizer = new TrimStringsDenormalizer($trimmer, $except);
        $denormalizer->setDenormalizer($innerDenormalizer);

        $result = $denormalizer->denormalize($data, $type, $format, $context);

        self::assertSame($expectedResult, $result);
    }

    /**
     * @param bool $expected
     * @param mixed $data
     * @param string $type
     * @param string|null $format
     * @param mixed[]|null $context
     *
     * @dataProvider provideDataForSupportsDenormalization
     */
    public function testSupportsDenormalizationReturnsExpectedResult(
        bool $expected,
        $data,
        string $type,
        ?string $format = null,
        ?array $context = null
    ): void {
        /** @var \EonX\EasyCore\Helpers\StringsTrimmerInterface $trimmer */
        $trimmer = $this->mock(StringsTrimmerInterface::class);
        $denormalizer = new TrimStringsDenormalizer($trimmer);

        $result = $denormalizer->supportsDenormalization($data, $type, $format, $context);

        self::assertSame($expected, $result);
    }
}
