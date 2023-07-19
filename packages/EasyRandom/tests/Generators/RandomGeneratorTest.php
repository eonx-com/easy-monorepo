<?php

declare(strict_types=1);

namespace EonX\EasyRandom\Tests\Generators;

use EonX\EasyRandom\Bridge\Symfony\Generators\SymfonyUuidV6Generator;
use EonX\EasyRandom\Generators\RandomGenerator;
use EonX\EasyRandom\Interfaces\RandomIntegerGeneratorInterface;
use EonX\EasyRandom\Interfaces\RandomStringGeneratorInterface;
use EonX\EasyRandom\Interfaces\RandomStringInterface;
use EonX\EasyRandom\Interfaces\UuidGeneratorInterface;
use EonX\EasyRandom\Tests\AbstractTestCase;
use EonX\EasyRandom\ValueObject\RandomString;

final class RandomGeneratorTest extends AbstractTestCase
{
    public function testIntegerSucceeds(): void
    {
        $randomIntegerGenerator = new class() implements RandomIntegerGeneratorInterface {
            public function generate(?int $min = null, ?int $max = null): int
            {
                return ($min ?? 0) + ($max ?? 0);
            }
        };
        $sut = new RandomGenerator(
            uuidGenerator: new SymfonyUuidV6Generator(),
            randomIntegerGenerator: $randomIntegerGenerator
        );

        $result = $sut->integer(100, 23);

        self::assertSame(123, $result);
    }

    public function testStringSucceeds(): void
    {
        $randomStringGenerator = new class() implements RandomStringGeneratorInterface {
            public function generate(int $length): RandomStringInterface
            {
                return new RandomString($length);
            }
        };
        $sut = new RandomGenerator(
            uuidGenerator: new SymfonyUuidV6Generator(),
            randomStringGenerator: $randomStringGenerator
        );

        $result = $sut->string(100);

        self::assertEquals(new RandomString(100), $result);
    }

    public function testUuidSucceeds(): void
    {
        $uuidGenerator = new class() implements UuidGeneratorInterface {
            public function generate(): string
            {
                return 'some-uuid';
            }
        };
        $sut = new RandomGenerator($uuidGenerator);

        $result = $sut->uuid();

        self::assertSame('some-uuid', $result);
    }
}
