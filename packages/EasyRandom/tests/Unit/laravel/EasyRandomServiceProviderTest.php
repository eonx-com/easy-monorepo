<?php
declare(strict_types=1);

namespace EonX\EasyRandom\Tests\Unit\Laravel;

use EonX\EasyRandom\Generator\RamseyUuidV4Generator;
use EonX\EasyRandom\Generator\RamseyUuidV6Generator;
use EonX\EasyRandom\Generator\RandomGenerator;
use EonX\EasyRandom\Generator\RandomGeneratorInterface;
use EonX\EasyRandom\Generator\RandomIntegerGenerator;
use EonX\EasyRandom\Generator\RandomIntegerGeneratorInterface;
use EonX\EasyRandom\Generator\RandomStringGenerator;
use EonX\EasyRandom\Generator\RandomStringGeneratorInterface;
use EonX\EasyRandom\Generator\UuidGeneratorInterface;
use PHPUnit\Framework\Attributes\DataProvider;

final class EasyRandomServiceProviderTest extends AbstractLumenTestCase
{
    /**
     * @see testUuidGeneratorInstance
     */
    public static function provideConfigsForUuidGenerator(): iterable
    {
        yield 'UUID v4' => [
            'config' => [
                'easy-random' => [
                    'uuid_version' => 4,
                ],
            ],
            'expectedUuidGeneratorClass' => RamseyUuidV4Generator::class,
        ];

        yield 'UUID v6' => [
            'config' => [
                'easy-random' => [
                    'uuid_version' => 6,
                ],
            ],
            'expectedUuidGeneratorClass' => RamseyUuidV6Generator::class,
        ];
    }

    public function testRandomGeneratorInstance(): void
    {
        $sut = $this->getApp();

        $result = $sut->get(RandomGeneratorInterface::class);

        self::assertInstanceOf(RandomGenerator::class, $result);
        self::assertInstanceOf(
            RandomStringGenerator::class,
            self::getPrivatePropertyValue($result, 'randomStringGenerator')
        );
        self::assertInstanceOf(
            RandomIntegerGenerator::class,
            self::getPrivatePropertyValue($result, 'randomIntegerGenerator')
        );
        self::assertInstanceOf(RamseyUuidV6Generator::class, self::getPrivatePropertyValue($result, 'uuidGenerator'));
    }

    public function testRandomIntegerGeneratorInstance(): void
    {
        $sut = $this->getApp();

        $result = $sut->get(RandomIntegerGeneratorInterface::class);

        self::assertInstanceOf(RandomIntegerGenerator::class, $result);
    }

    public function testRandomStringGeneratorInstance(): void
    {
        $sut = $this->getApp();

        $result = $sut->get(RandomStringGeneratorInterface::class);

        self::assertInstanceOf(RandomStringGenerator::class, $result);
    }

    /**
     * @param string[] $config
     *
     * @psalm-param class-string $expectedUuidGeneratorClass
     */
    #[DataProvider('provideConfigsForUuidGenerator')]
    public function testUuidGeneratorInstance(array $config, string $expectedUuidGeneratorClass): void
    {
        $sut = $this->getApp($config);

        $result = $sut->get(UuidGeneratorInterface::class);

        self::assertInstanceOf($expectedUuidGeneratorClass, $result);
    }
}
