<?php

declare(strict_types=1);

namespace EonX\EasyRandom\Tests\Bridge\Symfony;

use EonX\EasyRandom\Interfaces\RandomGeneratorInterface;
use EonX\EasyRandom\RandomGenerator;
use EonX\EasyRandom\UuidV4\RamseyUuidV4Generator;
use EonX\EasyRandom\UuidV4\SymfonyUidUuidV4Generator;
use EonX\EasyRandom\UuidV6\RamseyUuidV6Generator;
use EonX\EasyRandom\UuidV6\SymfonyUidUuidV6Generator;

final class EasyRandomBundleTest extends AbstractSymfonyTestCase
{
    public function testSanity(): void
    {
        $randomGenerator = $this->getKernel()
            ->getContainer()
            ->get(RandomGeneratorInterface::class);

        self::assertInstanceOf(RandomGeneratorInterface::class, $randomGenerator);
        self::assertInstanceOf(RandomGenerator::class, $randomGenerator);
    }

    /**
     * @param string[] $configs
     * @param class-string $expectedUuidV4GeneratorClass
     * @param class-string $expectedUuidV6GeneratorClass
     *
     * @dataProvider provideConfigsToTestUuidGeneratorInstances
     */
    public function testUuidGeneratorInstances(
        array $configs,
        string $expectedUuidV4GeneratorClass,
        string $expectedUuidV6GeneratorClass
    ): void {
        $randomGenerator = $this->getKernel($configs)
            ->getContainer()
            ->get(RandomGeneratorInterface::class);

        $uuidV4Generator = $this->getPrivatePropertyValue($randomGenerator, 'uuidV4Generator');
        $uuidV6Generator = $this->getPrivatePropertyValue($randomGenerator, 'uuidV6Generator');

        self::assertInstanceOf($expectedUuidV4GeneratorClass, $uuidV4Generator);
        self::assertInstanceOf($expectedUuidV6GeneratorClass, $uuidV6Generator);
    }

    /**
     * @return iterable<mixed>
     *
     * @see testUuidGeneratorInstances
     */
    protected function provideConfigsToTestUuidGeneratorInstances(): iterable
    {
        yield 'Default' => [
            'configs' => [],
            'expectedUuidV4GeneratorClass' => RamseyUuidV4Generator::class,
            'expectedUuidV6GeneratorClass' => RamseyUuidV6Generator::class,
        ];

        yield 'Ramsey\Uuid' => [
            'configs' => [__DIR__ . '/Fixtures/config/ramsey_uuid.yaml'],
            'expectedUuidV4GeneratorClass' => RamseyUuidV4Generator::class,
            'expectedUuidV6GeneratorClass' => RamseyUuidV6Generator::class,
        ];

        yield 'Symfony\Uid' => [
            'configs' => [__DIR__ . '/Fixtures/config/symfony_uid_uuid.yaml'],
            'expectedUuidV4GeneratorClass' => SymfonyUidUuidV4Generator::class,
            'expectedUuidV6GeneratorClass' => SymfonyUidUuidV6Generator::class,
        ];
    }
}
