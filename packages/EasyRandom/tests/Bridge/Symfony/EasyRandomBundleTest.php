<?php
declare(strict_types=1);

namespace EonX\EasyRandom\Tests\Bridge\Symfony;

use EonX\EasyRandom\Generators\RandomGenerator;
use EonX\EasyRandom\Generators\RandomIntegerGenerator;
use EonX\EasyRandom\Generators\RandomStringGenerator;
use EonX\EasyRandom\Generators\UuidGenerator;
use EonX\EasyRandom\Interfaces\RandomGeneratorInterface;
use EonX\EasyRandom\Interfaces\RandomIntegerGeneratorInterface;
use EonX\EasyRandom\Interfaces\RandomStringGeneratorInterface;
use EonX\EasyRandom\Interfaces\UuidGeneratorInterface;
use Symfony\Component\Uid\UuidV7;

final class EasyRandomBundleTest extends AbstractSymfonyTestCase
{
    public function testRandomGeneratorInstance(): void
    {
        $result = self::getService(RandomGeneratorInterface::class);

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
        $result = self::getService(RandomIntegerGeneratorInterface::class);

        self::assertInstanceOf(RandomIntegerGenerator::class, $result);
    }

    public function testRandomStringGeneratorInstance(): void
    {
        $result = self::getService(RandomStringGeneratorInterface::class);

        self::assertInstanceOf(RandomStringGenerator::class, $result);
    }

    public function testUuidGeneratorInstance(): void
    {
        $result = self::getService(UuidGeneratorInterface::class);

        $uuidFactory = self::getPrivatePropertyValue($result, 'uuidFactory');
        self::assertSame(UuidV7::class, self::getPrivatePropertyValue($uuidFactory, 'defaultClass'));
    }
}
