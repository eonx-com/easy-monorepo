<?php

declare(strict_types=1);

namespace EonX\EasyCore\Tests\Bridge\Symfony\Serializer;

use EonX\EasyCore\Bridge\Symfony\Serializer\TrimStringsNormalizer;
use EonX\EasyCore\Tests\Bridge\Symfony\AbstractSymfonyTestCase;
use stdClass;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class TrimStringsNormalizerTest extends AbstractSymfonyTestCase
{
    /**
     * @return mixed[]
     *
     * @see testSupportsDenormalizationSucceeds
     */
    public function dataProviderForSupportsDenormalization(): array
    {
        return [
            'data is string' => [
                '',
                true,
            ],
            'data is array' => [
                [],
                true,
            ],
            'data is bool' => [
                true,
                false,
            ],
            'data is numeric' => [
                123,
                false,
            ],
            'data is object' => [
                new stdClass(),
                false,
            ],
        ];
    }

    /**
     * @param mixed $data
     *
     * @dataProvider dataProviderForSupportsDenormalization
     */
    public function testSupportsDenormalizationSucceeds($data, bool $expectedResult): void
    {
        $decorated = $this->prophesize(DenormalizerInterface::class);
        $normalizer = new TrimStringsNormalizer($decorated->reveal());

        $result = $normalizer->supportsDenormalization($data, 'no-matter');

        self::assertSame($expectedResult, $result);
    }

    /**
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function testDenormalizeSucceeds(): void
    {
        $data = [
            'abc' => '  123  ',
            'xyz' => '  456  ',
            'foo' => '  abc  ',
            'bar' => '  ZXY  ',
            'integer' => 123,
            'recursion' => [
                '  123  ',
                '  456  ',
                'null' => null,
            ],
            'recursion_with_2_level' => [
                'recursion' => [
                    '  abc  ',
                    '  ZXY  ',
                    'object' => new stdClass(),
                ],
                '  123  ',
                '  456  ',
            ],
        ];
        $type = 'no-matter';
        $format = null;
        $context = [];
        $expectedData = [
            'abc' => '123',
            'xyz' => '456',
            'foo' => 'abc',
            'bar' => 'ZXY',
            'integer' => 123,
            'recursion' => [
                '123',
                '456',
                'null' => null,
            ],
            'recursion_with_2_level' => [
                'recursion' => [
                    'abc',
                    'ZXY',
                    'object' => new stdClass(),
                ],
                '123',
                '456',
            ],
        ];
        $decorated = $this->prophesize(DenormalizerInterface::class);
        $decorated->denormalize($expectedData, $type, $format, $context)->willReturn($expectedData);
        $normalizer = new TrimStringsNormalizer($decorated->reveal());

        $normalizer->denormalize($data, $type, $format, $context);

        $decorated->denormalize($expectedData, $type, $format, $context)->shouldHaveBeenCalledOnce();
    }
}
