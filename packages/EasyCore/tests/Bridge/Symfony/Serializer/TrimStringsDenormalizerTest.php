<?php

declare(strict_types=1);

namespace EonX\EasyCore\Tests\Bridge\Symfony\Serializer;

use EonX\EasyCore\Bridge\Symfony\Serializer\TrimStringsDenormalizer;
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
     * @see testSupportsDenormalizationReturnsTrue
     */
    public function provideCorrectDataForDenormalization(): array
    {
        return [
            'Context has not `TRIM_STRINGS_ALREADY_CALLED` key' => [
                'data' => 'some-correct-value',
                'type' => 'no-matter',
                'format' => 'no-matter',
                'context' => [
                    'some-key' => 'some-value',
                ],
            ],
            'Data is an array' => [
                'data' => [],
                'type' => 'no-matter',
                'format' => 'no-matter',
                'context' => null,
            ],
            'Data is a string' => [
                'data' => 'some-correct-value',
                'type' => 'no-matter',
                'format' => 'no-matter',
                'context' => null,
            ],
        ];
    }

    /**
     * @return mixed[]
     *
     * @see testDenormalizeSucceedsWithTrimValue
     */
    public function provideDataForDenormalize(): array
    {
        return [
            'data is string' => ['', ''],
            'data is array' => [[], []],
        ];
    }

    /**
     * @return mixed[]
     *
     * @see testSupportsDenormalizationReturnsFalse
     */
    public function provideIneligibleDataForDenormalization(): array
    {
        return [
            'Already called' => [
                'data' => 'some-correct-value',
                'type' => 'no-matter',
                'format' => 'no-matter',
                'context' => [
                    'TRIM_STRINGS_ALREADY_CALLED' => true,
                ],
            ],
            'Data is an object' => [
                'data' => new stdClass(),
                'type' => 'no-matter',
                'format' => 'no-matter',
                'context' => null,
            ],
            'Data is an integer' => [
                'data' => 123,
                'type' => 'no-matter',
                'format' => 'no-matter',
                'context' => null,
            ],
            'Data is a float' => [
                'data' => 12.34,
                'type' => 'no-matter',
                'format' => 'no-matter',
                'context' => null,
            ],
            'Data is a bool' => [
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
        $expectation = static function (MockInterface $mock): void {
            $mock->shouldNotReceive('trim');
        };
        if (\is_string($data) || \is_array($data)) {
            $expectation = static function (MockInterface $mock) use ($data, $except, $expectedResult): void {
                $mock->shouldReceive('trim')->once()->with($data, $except)->andReturn($expectedResult);
            };
        }
        /** @var \EonX\EasyCore\Helpers\StringsTrimmerInterface $trimmer */
        $trimmer = $this->mock(StringsTrimmerInterface::class, $expectation);
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
                        \array_merge($context, ['TRIM_STRINGS_ALREADY_CALLED' => true])
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
     * @param mixed $data
     * @param string $type
     * @param string|null $format
     * @param mixed[]|null $context
     *
     * @dataProvider provideIneligibleDataForDenormalization
     */
    public function testSupportsDenormalizationReturnsFalse(
        $data,
        string $type,
        ?string $format = null,
        ?array $context = null
    ): void {
        /** @var \EonX\EasyCore\Helpers\StringsTrimmerInterface $trimmer */
        $trimmer = $this->mock(StringsTrimmerInterface::class);
        $denormalizer = new TrimStringsDenormalizer($trimmer);

        $result = $denormalizer->supportsDenormalization($data, $type, $format, $context);

        self::assertFalse($result);
    }

    /**
     * @param mixed $data
     * @param string $type
     * @param string|null $format
     * @param mixed[]|null $context
     *
     * @dataProvider provideCorrectDataForDenormalization
     */
    public function testSupportsDenormalizationReturnsTrue(
        $data,
        string $type,
        ?string $format = null,
        ?array $context = null
    ): void {
        /** @var \EonX\EasyCore\Helpers\StringsTrimmerInterface $trimmer */
        $trimmer = $this->mock(StringsTrimmerInterface::class);
        $denormalizer = new TrimStringsDenormalizer($trimmer);

        $result = $denormalizer->supportsDenormalization($data, $type, $format, $context);

        self::assertTrue($result);
    }
}
