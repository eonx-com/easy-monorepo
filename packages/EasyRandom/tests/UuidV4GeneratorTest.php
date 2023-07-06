<?php

declare(strict_types=1);

namespace EonX\EasyRandom\Tests;

use EonX\EasyRandom\Exceptions\UuidV4GeneratorNotSetException;
use EonX\EasyRandom\Interfaces\UuidV4GeneratorInterface;
use EonX\EasyRandom\RandomGenerator;
use EonX\EasyRandom\UuidV4\RamseyUuidV4Generator;
use EonX\EasyRandom\UuidV4\SymfonyUidUuidV4Generator;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Uid\UuidV4;

final class UuidV4GeneratorTest extends AbstractTestCase
{
    /**
     * @return iterable<mixed>
     *
     * @see testUuidV4
     */
    public static function providerTestUuidV4(): iterable
    {
        yield 'Ramsey\Uuid' => [new RamseyUuidV4Generator()];
        yield 'Symfony\Uid' => [new SymfonyUidUuidV4Generator()];
    }

    /**
     * @dataProvider providerTestUuidV4
     */
    public function testUuidV4(UuidV4GeneratorInterface $uuidV4Generator): void
    {
        $randomGenerator = (new RandomGenerator())->setUuidV4Generator($uuidV4Generator);

        for ($i = 0; $i < 100; $i++) {
            $uuidV4 = $randomGenerator->uuidV4();

            self::assertTrue(Uuid::isValid($uuidV4));
            self::assertTrue(UuidV4::isValid($uuidV4));
        }
    }

    public function testUuidV4GeneratorNotSetExceptionThrown(): void
    {
        $this->expectException(UuidV4GeneratorNotSetException::class);

        (new RandomGenerator())->uuidV4();
    }
}
