<?php

declare(strict_types=1);

namespace EonX\EasyRandom\Tests\Bridge\Laravel;

use EonX\EasyRandom\Interfaces\RandomGeneratorInterface;
use EonX\EasyRandom\Interfaces\UuidV4GeneratorInterface;
use EonX\EasyRandom\UuidV4\RamseyUuidV4Generator;
use EonX\EasyRandom\UuidV4\SymfonyUidUuidV4Generator;

final class EasyRandomServiceProviderTest extends AbstractLumenTestCase
{
    /**
     * @return iterable<mixed>
     *
     * @see testUuidV4GeneratorInstance
     */
    public function providerTestUuidV4GeneratorInstance(): iterable
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
    public function testUuidV4GeneratorInstance(UuidV4GeneratorInterface $uuidV4Generator): void
    {
        $app = $this->getApp();
        $app->extend(
            RandomGeneratorInterface::class,
            static function (RandomGeneratorInterface $randomGenerator) use (
                $uuidV4Generator
            ): RandomGeneratorInterface {
                return $randomGenerator->setUuidV4Generator($uuidV4Generator);
            }
        );

        $randomGenerator = $app->get(RandomGeneratorInterface::class);

        $setUuidV4Generator = \Closure::bind(function () {
            return $this->uuidV4Generator;
        }, $randomGenerator, $randomGenerator)();

        self::assertEquals(\spl_object_hash($uuidV4Generator), \spl_object_hash($setUuidV4Generator));
    }
}
