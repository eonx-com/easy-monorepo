<?php

declare(strict_types=1);

namespace EonX\EasyRandom\Tests\Bridge\Symfony;

use Closure;
use EonX\EasyRandom\Interfaces\RandomGeneratorInterface;
use EonX\EasyRandom\UuidV4\RamseyUuidV4Generator;
use EonX\EasyRandom\UuidV4\SymfonyUidUuidV4Generator;

final class EasyRandomBundleTest extends AbstractSymfonyTestCase
{
    /**
     * @return iterable<mixed>
     *
     * @see testUuidV4GeneratorInstance
     */
    public static function providerTestUuidV4GeneratorInstance(): iterable
    {
        yield 'Ramsey\Uuid' => [[__DIR__ . '/Fixtures/config/ramsey_uuid_v4.yaml'], RamseyUuidV4Generator::class];

        yield 'Symfony\Uid' => [
            [__DIR__ . '/Fixtures/config/symfony_uid_uuid_v4.yaml'],
            SymfonyUidUuidV4Generator::class,
        ];
    }

    public function testSanity(): void
    {
        $randomGenerator = $this->getKernel()
            ->getContainer()
            ->get(RandomGeneratorInterface::class);

        self::assertInstanceOf(RandomGeneratorInterface::class, $randomGenerator);
    }

    /**
     * @param string[] $configs
     *
     * @dataProvider providerTestUuidV4GeneratorInstance
     *
     * @psalm-param class-string $uuidV4GeneratorClass
     */
    public function testUuidV4GeneratorInstance(array $configs, string $uuidV4GeneratorClass): void
    {
        $randomGenerator = $this->getKernel($configs)
            ->getContainer()
            ->get(RandomGeneratorInterface::class);

        $uuidV4Generator = Closure::bind(fn () => $this->uuidV4Generator, $randomGenerator, $randomGenerator)();

        self::assertInstanceOf($uuidV4GeneratorClass, $uuidV4Generator);
    }
}
