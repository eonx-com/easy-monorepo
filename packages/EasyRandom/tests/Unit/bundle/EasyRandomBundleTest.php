<?php
declare(strict_types=1);

namespace EonX\EasyRandom\Tests\Unit\Bundle;

use EonX\EasyRandom\Generator\RandomGenerator;
use EonX\EasyRandom\Generator\RandomGeneratorInterface;
use EonX\EasyRandom\Generator\RandomIntegerGenerator;
use EonX\EasyRandom\Generator\RandomIntegerGeneratorInterface;
use EonX\EasyRandom\Generator\RandomStringGenerator;
use EonX\EasyRandom\Generator\RandomStringGeneratorInterface;
use EonX\EasyRandom\Generator\UuidGenerator;
use EonX\EasyRandom\Generator\UuidGeneratorInterface;
use EonX\EasyRandom\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Uid\UuidV4;
use Symfony\Component\Uid\UuidV6;

final class EasyRandomBundleTest extends AbstractUnitTestCase
{
    /**
     * @see testUuidGeneratorInstance
     */
    public static function provideConfigsForUuidGenerator(): iterable
    {
        yield 'UUID v4' => [
            'environment' => 'test_v4',
            'expectedUuidFactoryClass' => UuidV4::class,
        ];

        yield 'UUID v6' => [
            'environment' => 'test_v6',
            'expectedUuidFactoryClass' => UuidV6::class,
        ];
    }

    public function testRandomGeneratorInstance(): void
    {
        $sut = self::getService(RandomGeneratorInterface::class);

        self::assertInstanceOf(RandomGenerator::class, $sut);
        self::assertInstanceOf(
            RandomStringGenerator::class,
            self::getPrivatePropertyValue($sut, 'randomStringGenerator')
        );
        self::assertInstanceOf(
            RandomIntegerGenerator::class,
            self::getPrivatePropertyValue($sut, 'randomIntegerGenerator')
        );
        self::assertInstanceOf(UuidGenerator::class, self::getPrivatePropertyValue($sut, 'uuidGenerator'));
    }

    public function testRandomIntegerGeneratorInstance(): void
    {
        $sut = self::getService(RandomIntegerGeneratorInterface::class);

        self::assertInstanceOf(RandomIntegerGenerator::class, $sut);
    }

    public function testRandomStringGeneratorInstance(): void
    {
        $sut = self::getService(RandomStringGeneratorInterface::class);

        self::assertInstanceOf(RandomStringGenerator::class, $sut);
    }

    #[DataProvider('provideConfigsForUuidGenerator')]
    public function testUuidGeneratorInstance(string $environment, string $expectedUuidFactoryClass): void
    {
        self::bootKernel([
            'environment' => $environment,
        ]);
        $uuidGenerator = self::getService(UuidGeneratorInterface::class);

        /** @var object $sut */
        $sut = self::getPrivatePropertyValue($uuidGenerator, 'uuidFactory');

        self::assertSame($expectedUuidFactoryClass, self::getPrivatePropertyValue($sut, 'defaultClass'));
    }
}
