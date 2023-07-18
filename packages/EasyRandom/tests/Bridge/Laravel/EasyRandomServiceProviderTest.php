<?php

declare(strict_types=1);

namespace EonX\EasyRandom\Tests\Bridge\Laravel;

use EonX\EasyRandom\Generators\RamseyUuidV4Generator;
use EonX\EasyRandom\Generators\SymfonyUidUuidV4Generator;
use EonX\EasyRandom\Interfaces\RandomGeneratorInterface;
use EonX\EasyRandom\Interfaces\UuidGeneratorInterface;

final class EasyRandomServiceProviderTest extends AbstractLumenTestCase
{
    /**
     * @return iterable<mixed>
     *
     * @see testUuidV4GeneratorInstance
     */
    public static function providerTestUuidV4GeneratorInstance(): iterable
    {
        yield 'Ramsey\Uuid' => [new RamseyUuidV4Generator()];
        yield 'Symfony\Uid' => [new SymfonyUidUuidV4Generator()];
    }

    public function testSanity(): void
    {
        $randomGenerator = $this->getApp()
            ->get(RandomGeneratorInterface::class);

        self::assertInstanceOf(RandomGeneratorInterface::class, $randomGenerator);
    }

    /**
     * @dataProvider providerTestUuidV4GeneratorInstance
     */
    public function testUuidV4GeneratorInstance(UuidGeneratorInterface $uuidV4Generator): void
    {
        $app = $this->getApp();
        $app->extend(
            RandomGeneratorInterface::class,
            static fn (
                RandomGeneratorInterface $randomGenerator,
            ): RandomGeneratorInterface => $randomGenerator->setUuidV4Generator($uuidV4Generator)
        );

        $randomGenerator = $app->get(RandomGeneratorInterface::class);

        $actualUuidV4Generator = $this->getPrivatePropertyValue($randomGenerator, 'uuidV4Generator');
        self::assertEquals(\spl_object_hash($uuidV4Generator), \spl_object_hash($actualUuidV4Generator));
    }
}
