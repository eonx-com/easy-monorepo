<?php

declare(strict_types=1);

namespace EonX\EasyRandom\Tests\Bridge\Laravel;

use EonX\EasyRandom\Interfaces\RandomGeneratorInterface;
use EonX\EasyRandom\RandomGenerator;
use EonX\EasyRandom\UuidV4\RamseyUuidV4Generator;
use EonX\EasyRandom\UuidV4\SymfonyUidUuidV4Generator;
use EonX\EasyRandom\UuidV6\RamseyUuidV6Generator;
use EonX\EasyRandom\UuidV6\SymfonyUidUuidV6Generator;

final class EasyRandomServiceProviderTest extends AbstractLumenTestCase
{
    public function testSanity(): void
    {
        $randomGenerator = $this->getApplication()
            ->get(RandomGeneratorInterface::class);

        self::assertInstanceOf(RandomGeneratorInterface::class, $randomGenerator);
        self::assertInstanceOf(RandomGenerator::class, $randomGenerator);
    }

    /**
     * @param array<string, mixed> $config
     * @param class-string $expectedUuidV4GeneratorClass
     * @param class-string $expectedUuidV6GeneratorClass
     *
     * @dataProvider provideConfigsToTestUuidGeneratorInstances
     */
    public function testUuidGeneratorInstances(
        array $config,
        string $expectedUuidV4GeneratorClass,
        string $expectedUuidV6GeneratorClass
    ): void {
        $app = $this->getApplication($config);

        $randomGenerator = $app->get(RandomGeneratorInterface::class);

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
            'config' => [
                'easy-random' => [],
            ],
            'expectedUuidV4GeneratorClass' => RamseyUuidV4Generator::class,
            'expectedUuidV6GeneratorClass' => RamseyUuidV6Generator::class,
        ];
        yield 'Ramsey\Uuid' => [
            'config' => [
                'easy-random' => [
                    'uuid_v4_generator' => RamseyUuidV4Generator::class,
                    'uuid_v6_generator' => RamseyUuidV6Generator::class,
                ],
            ],
            'expectedUuidV4GeneratorClass' => RamseyUuidV4Generator::class,
            'expectedUuidV6GeneratorClass' => RamseyUuidV6Generator::class,
        ];
        yield 'Symfony\Uid' => [
            'config' => [
                'easy-random' => [
                    'uuid_v4_generator' => SymfonyUidUuidV4Generator::class,
                    'uuid_v6_generator' => SymfonyUidUuidV6Generator::class,
                ],
            ],
            'expectedUuidV4GeneratorClass' => SymfonyUidUuidV4Generator::class,
            'expectedUuidV6GeneratorClass' => SymfonyUidUuidV6Generator::class,
        ];
    }
}
