<?php

declare(strict_types=1);

namespace EonX\EasyCore\Tests\Bridge\Symfony\Serializer;

use EonX\EasyCore\Bridge\Symfony\Serializer\TrimStringsNormalizer;
use EonX\EasyCore\Helpers\StringsTrimmerInterface;
use EonX\EasyCore\Tests\Bridge\Symfony\AbstractSymfonyTestCase;
use EonX\EasyCore\Tests\Bridge\Symfony\Stubs\NormalizerStub;
use Mockery\MockInterface;
use stdClass;

/**
 * @covers \EonX\EasyCore\Bridge\Symfony\Serializer\TrimStringsNormalizer
 *
 * @internal
 */
final class TrimStringsNormalizerTest extends AbstractSymfonyTestCase
{
    /**
     * @return mixed[]
     *
     * @see testDenormalizeSucceedsWithTrimValue
     */
    public function provideDataForDenormalize(): array
    {
        $object = new stdClass();

        return [
            'data is string' => [
                '',
                '',
            ],
            'data is array' => [
                [],
                [],
            ],
            'data is bool' => [
                true,
                true,
            ],
            'data is numeric' => [
                123,
                123,
            ],
            'data is object' => [
                $object,
                $object,
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
        $trimmer = $this->mock(
            StringsTrimmerInterface::class,
            $expectation
        );
        $decorated = new NormalizerStub();
        $denormalizer = new TrimStringsNormalizer($decorated, $trimmer, $except);

        $result = $denormalizer->denormalize($data, $type, $format, $context);

        self::assertSame($expectedResult, $result);
    }
}
