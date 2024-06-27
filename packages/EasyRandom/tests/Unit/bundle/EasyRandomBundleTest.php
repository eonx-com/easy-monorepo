<?php
declare(strict_types=1);

namespace EonX\EasyRandom\Tests\Unit\Bundle;

use EonX\EasyRandom\Generator\RandomGenerator;
use EonX\EasyRandom\Generator\RandomGeneratorInterface;
use EonX\EasyRandom\Generator\RandomIntegerGenerator;
use EonX\EasyRandom\Generator\RandomIntegerGeneratorInterface;
use EonX\EasyRandom\Generator\RandomStringGenerator;
use EonX\EasyRandom\Generator\RandomStringGeneratorInterface;
use EonX\EasyRandom\Generator\SymfonyUuidV4Generator;
use EonX\EasyRandom\Generator\SymfonyUuidV6Generator;
use EonX\EasyRandom\Generator\UuidGeneratorInterface;
use PHPUnit\Framework\Attributes\DataProvider;

final class EasyRandomBundleTest extends AbstractSymfonyTestCase
{
    /**
     * @see testUuidGeneratorInstance
     */
    public static function provideConfigsForUuidGenerator(): iterable
    {
        yield 'UUID v4' => [
            'configs' => [__DIR__ . '/../../Fixture/config/uuid_version_4.php'],
            'expectedUuidGeneratorClass' => SymfonyUuidV4Generator::class,
        ];

        yield 'UUID v6' => [
            'configs' => [__DIR__ . '/../../Fixture/config/uuid_version_6.php'],
            'expectedUuidGeneratorClass' => SymfonyUuidV6Generator::class,
        ];
    }

    public function testRandomGeneratorInstance(): void
    {
        $sut = $this->getKernel([])
            ->getContainer();

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
        self::assertInstanceOf(SymfonyUuidV6Generator::class, self::getPrivatePropertyValue($result, 'uuidGenerator'));
    }

    public function testRandomIntegerGeneratorInstance(): void
    {
        $sut = $this->getKernel([])
            ->getContainer();

        $result = $sut->get(RandomIntegerGeneratorInterface::class);

        self::assertInstanceOf(RandomIntegerGenerator::class, $result);
    }

    public function testRandomStringGeneratorInstance(): void
    {
        $sut = $this->getKernel([])
            ->getContainer();

        $result = $sut->get(RandomStringGeneratorInterface::class);

        self::assertInstanceOf(RandomStringGenerator::class, $result);
    }

    /**
     * @param string[] $configs
     *
     * @psalm-param class-string $expectedUuidGeneratorClass
     */
    #[DataProvider('provideConfigsForUuidGenerator')]
    public function testUuidGeneratorInstance(array $configs, string $expectedUuidGeneratorClass): void
    {
        $sut = $this->getKernel($configs)
            ->getContainer();

        $result = $sut->get(UuidGeneratorInterface::class);

        self::assertInstanceOf($expectedUuidGeneratorClass, $result);
    }
}
