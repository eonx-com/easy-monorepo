<?php

declare(strict_types=1);

namespace EonX\EasyCore\Tests\Bridge\Symfony\Serializer;

use EonX\EasyCore\Bridge\Symfony\Serializer\TrimStringsDenormalizer;
use EonX\EasyCore\Tests\Bridge\Symfony\AbstractSymfonyTestCase;
use EonX\EasyCore\Tests\Bridge\Symfony\Stubs\DenormalizerInterfaceStub;
use EonX\EasyCore\Tests\Helpers\CleanerInterface;
use stdClass;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class TrimStringsNormalizerTest extends AbstractSymfonyTestCase
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
        $cleaner = $this->prophesize(CleanerInterface::class);
        $decorated = $this->prophesize(DenormalizerInterface::class);
        $normalizer = new TrimStringsDenormalizer($decorated->reveal(), $cleaner->reveal());

        $result = $normalizer->supportsDenormalization($data, 'no-matter');

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
        $expectedData = ['abc' => '123'];
        $cleaner = $this->prophesize(CleanerInterface::class);
        $cleaner->clean($data, $except)->willReturn($expectedData);
        $decorated = new DenormalizerInterfaceStub();
        $normalizer = new TrimStringsDenormalizer($decorated, $cleaner->reveal(), $except);

        $result = $normalizer->denormalize($data, $type, $format, $context);

        self::assertSame($expectedData, $result);
        $cleaner->clean($data, $except)->shouldHaveBeenCalledOnce();
    }
}
