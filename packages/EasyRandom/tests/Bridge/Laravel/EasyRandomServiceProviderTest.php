<?php
declare(strict_types=1);

namespace EonX\EasyRandom\Tests\Bridge\Laravel;

use EonX\EasyRandom\Generators\RandomGenerator;
use EonX\EasyRandom\Generators\RandomIntegerGenerator;
use EonX\EasyRandom\Generators\RandomStringGenerator;
use EonX\EasyRandom\Generators\UuidGenerator;
use EonX\EasyRandom\Interfaces\RandomGeneratorInterface;
use EonX\EasyRandom\Interfaces\RandomIntegerGeneratorInterface;
use EonX\EasyRandom\Interfaces\RandomStringGeneratorInterface;
use EonX\EasyRandom\Interfaces\UuidGeneratorInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Uid\UuidV4;
use Symfony\Component\Uid\UuidV6;

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
            'expectedUuidClass' => UuidV4::class,
        ];

        yield 'UUID v6' => [
            'config' => [
                'easy-random' => [
                    'uuid_version' => 6,
                ],
            ],
            'expectedUuidClass' => UuidV6::class,
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
        self::assertInstanceOf(UuidGenerator::class, self::getPrivatePropertyValue($result, 'uuidGenerator'));
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
     * @param class-string $expectedUuidClass
     */
    #[DataProvider('provideConfigsForUuidGenerator')]
    public function testUuidGeneratorInstance(array $config, string $expectedUuidClass): void
    {
        $sut = $this->getApp($config);

        $result = $sut->get(UuidGeneratorInterface::class);

        $uuidFactory = self::getPrivatePropertyValue($result, 'uuidFactory');
        self::assertSame($expectedUuidClass, self::getPrivatePropertyValue($uuidFactory, 'defaultClass'));
    }
}
