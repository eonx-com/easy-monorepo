<?php

declare(strict_types=1);

namespace EonX\EasyRandom\Tests;

use EonX\EasyRandom\Enums\UuidVersion;
use EonX\EasyRandom\Exceptions\UuidV4GeneratorNotSetException;
use EonX\EasyRandom\Exceptions\UuidV6GeneratorNotSetException;
use EonX\EasyRandom\Interfaces\UuidV4GeneratorInterface;
use EonX\EasyRandom\Interfaces\UuidV6GeneratorInterface;
use EonX\EasyRandom\RandomGenerator;
use EonX\EasyRandom\UuidV4\RamseyUuidV4Generator;
use EonX\EasyRandom\UuidV4\SymfonyUidUuidV4Generator;
use EonX\EasyRandom\UuidV6\RamseyUuidV6Generator;
use EonX\EasyRandom\UuidV6\SymfonyUidUuidV6Generator;
use Ramsey\Uuid\Uuid as RamseyUuid;
use Symfony\Component\Uid\UuidV4 as SymfonyUuidV4;
use Symfony\Component\Uid\UuidV6 as SymfonyUuidV6;

final class UuidTest extends AbstractTestCase
{
    /**
     * @dataProvider provideUuidV4Generators
     */
    public function testUuidSucceedsForUuidV4(UuidV4GeneratorInterface $uuidV4Generator): void
    {
        $randomGenerator = new RandomGenerator(defaultUuidVersion: UuidVersion::V4, uuidV4Generator: $uuidV4Generator);

        $uuidV4 = $randomGenerator->uuid(UuidVersion::V4);
        $uuidDefaultVersion = $randomGenerator->uuid();

        self::assertTrue(RamseyUuid::isValid($uuidV4));
        self::assertTrue(RamseyUuid::isValid($uuidDefaultVersion));
        self::assertTrue(SymfonyUuidV4::isValid($uuidV4));
        self::assertTrue(SymfonyUuidV4::isValid($uuidDefaultVersion));
    }

    /**
     * @dataProvider provideUuidV6Generators
     */
    public function testUuidSucceedsForUuidV6(UuidV6GeneratorInterface $uuidV6Generator): void
    {
        $randomGenerator = new RandomGenerator(defaultUuidVersion: UuidVersion::V6, uuidV6Generator: $uuidV6Generator);

        $uuidV6 = $randomGenerator->uuid(UuidVersion::V6);
        $uuidDefaultVersion = $randomGenerator->uuid();

        self::assertTrue(RamseyUuid::isValid($uuidV6));
        self::assertTrue(RamseyUuid::isValid($uuidDefaultVersion));
        self::assertTrue(SymfonyUuidV6::isValid($uuidV6));
        self::assertTrue(SymfonyUuidV6::isValid($uuidDefaultVersion));
    }

    /**
     * @param class-string<\Throwable> $expectedException
     *
     * @dataProvider provideExceptionForUuidVersions
     */
    public function testUuidThrowsExceptionWhenGeneratorIsNotSet(UuidVersion $version, string $expectedException): void
    {
        $randomGenerator = new RandomGenerator();
        $this->expectException($expectedException);

        $randomGenerator->uuid($version);
    }

    /**
     * @return iterable<mixed>
     *
     * @see testUuidThrowsExceptionWhenGeneratorIsNotSet
     */
    protected function provideExceptionForUuidVersions(): iterable
    {
        yield 'UUID v4' => [
            'version' => UuidVersion::V4,
            'expectedException' => UuidV4GeneratorNotSetException::class,
        ];

        yield 'UUID v6' => [
            'version' => UuidVersion::V6,
            'expectedException' => UuidV6GeneratorNotSetException::class,
        ];
    }

    /**
     * @return iterable<mixed>
     *
     * @see testUuidSucceedsForUuidV4
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

    /**
     * @return iterable<mixed>
     *
     * @see testUuidSucceedsForUuidV6
     */
    protected function provideUuidV6Generators(): iterable
    {
        yield 'Ramsey\Uuid' => [
            'uuidV6Generator' => new RamseyUuidV6Generator(),
        ];
        yield 'Symfony\Uid' => [
            'uuidV6Generator' => new SymfonyUidUuidV6Generator(),
        ];
    }
}
