<?php

declare(strict_types=1);

namespace EonX\EasyCore\Tests\Bridge\Symfony\Serializer;

use EonX\EasyCore\Bridge\Symfony\Serializer\TrimStringsDenormalizer;
use EonX\EasyCore\Helpers\RecursiveStringsTrimmer;
use EonX\EasyCore\Tests\Bridge\Symfony\AbstractSymfonyTestCase;
use EonX\EasyCore\Tests\Bridge\Symfony\Stubs\DenormalizerInterfaceStub;
use EonX\EasyCore\Helpers\StringsTrimmerInterface;
use Mockery\MockInterface;
use stdClass;

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
     * @see testSupportsDenormalizationSucceeds
     */
    public function provideDataForSupportsDenormalization(): array
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
     * @dataProvider provideDataForSupportsDenormalization
     */
    public function testSupportsDenormalizationSucceeds($data, bool $expectedResult): void
    {
        $denormalizer = new TrimStringsDenormalizer(new DenormalizerInterfaceStub(), new RecursiveStringsTrimmer());

        $result = $denormalizer->supportsDenormalization($data, 'no-matter');

        self::assertSame($expectedResult, $result);
    }

    /**
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function testDenormalizeSucceeds(): void
    {
        $data = ['abc' => '  123  '];
        $type = 'no-matter';
        $format = null;
        $context = [];
        $except = ['some-key'];
        $expectedResult = ['abc' => '123'];
        /** @var \EonX\EasyCore\Helpers\StringsTrimmerInterface $cleaner */
        $cleaner = $this->mock(
            StringsTrimmerInterface::class,
            static function (MockInterface $mock) use ($data, $except, $expectedResult): void {
                $mock->shouldReceive('clean')->once()->with($data, $except)->andReturn($expectedResult);
            });
        $decorated = new DenormalizerInterfaceStub();
        $denormalizer = new TrimStringsDenormalizer($decorated, $cleaner, $except);

        $result = $denormalizer->denormalize($data, $type, $format, $context);

        self::assertSame($expectedResult, $result);
    }
}
