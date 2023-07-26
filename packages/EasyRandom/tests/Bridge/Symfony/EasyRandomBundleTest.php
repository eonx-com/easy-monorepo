<?php

declare(strict_types=1);

namespace EonX\EasyRandom\Tests\Bridge\Symfony;

use EonX\EasyRandom\Bridge\Symfony\Generators\SymfonyUuidV4Generator;
use EonX\EasyRandom\Bridge\Symfony\Generators\SymfonyUuidV6Generator;
use EonX\EasyRandom\Generators\RandomGenerator;
use EonX\EasyRandom\Generators\RandomIntegerGenerator;
use EonX\EasyRandom\Generators\RandomStringGenerator;
use EonX\EasyRandom\Interfaces\RandomGeneratorInterface;
use EonX\EasyRandom\Interfaces\RandomIntegerGeneratorInterface;
use EonX\EasyRandom\Interfaces\RandomStringGeneratorInterface;
use EonX\EasyRandom\Interfaces\UuidGeneratorInterface;

final class EasyRandomBundleTest extends AbstractSymfonyTestCase
{
    /**
     * @return iterable<mixed>
     *
     * @see testUuidGeneratorInstance
     */
    public static function provideConfigsForUuidGenerator(): iterable
    {
        yield 'UUID v4' => [
            'configs' => [__DIR__ . '/Fixtures/config/uuid_version_4.yaml'],
            'expectedUuidGeneratorClass' => SymfonyUuidV4Generator::class,
        ];

        yield 'UUID v6' => [
            'configs' => [__DIR__ . '/Fixtures/config/uuid_version_6.yaml'],
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
            $this->getPrivatePropertyValue($result, 'randomStringGenerator')
        );
        self::assertInstanceOf(
            RandomIntegerGenerator::class,
            $this->getPrivatePropertyValue($result, 'randomIntegerGenerator')
        );
        self::assertInstanceOf(SymfonyUuidV6Generator::class, $this->getPrivatePropertyValue($result, 'uuidGenerator'));
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
     *
     * @dataProvider provideConfigsForUuidGenerator
     */
    public function testUuidGeneratorInstance(array $configs, string $expectedUuidGeneratorClass): void
    {
        $sut = $this->getKernel($configs)
            ->getContainer();

        $result = $sut->get(UuidGeneratorInterface::class);

        self::assertInstanceOf($expectedUuidGeneratorClass, $result);
    }
}
