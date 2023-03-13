<?php

declare(strict_types=1);

namespace EonX\EasyRandom\Tests;

use EonX\EasyRandom\Exceptions\UuidV4GeneratorNotSetException;
use EonX\EasyRandom\Interfaces\UuidV4GeneratorInterface;
use EonX\EasyRandom\RandomGenerator;
use EonX\EasyRandom\UuidV4\RamseyUuidV4Generator;
use EonX\EasyRandom\UuidV4\SymfonyUidUuidV4Generator;
use Ramsey\Uuid\Uuid as RamseyUuid;
use Symfony\Component\Uid\UuidV4 as SymfonyUuidV4;

final class UuidV4Test extends AbstractTestCase
{
    /**
     * @dataProvider provideUuidV4Generators
     */
    public function testUuidV4Succeeds(UuidV4GeneratorInterface $uuidV4Generator): void
    {
        $randomGenerator = new RandomGenerator(uuidV4Generator: $uuidV4Generator);

        $uuidV4 = $randomGenerator->uuidV4();

        self::assertTrue(RamseyUuid::isValid($uuidV4));
        self::assertTrue(SymfonyUuidV4::isValid($uuidV4));
    }

    public function testUuidV4ThrowsUuidV4GeneratorNotSetException(): void
    {
        $randomGenerator = new RandomGenerator();
        $this->expectException(UuidV4GeneratorNotSetException::class);

        $randomGenerator->uuidV4();
    }

    /**
     * @return iterable<mixed>
     *
     * @see testUuidV4Succeeds
     */
    protected function provideUuidV4Generators(): iterable
    {
        yield 'Ramsey\Uuid' => [
            'uuidV4Generator' => new RamseyUuidV4Generator(),
        ];
        yield 'Symfony\Uid' => [
            'uuidV4Generator' => new SymfonyUidUuidV4Generator(),
        ];
    }
}
