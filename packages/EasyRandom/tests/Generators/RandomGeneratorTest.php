<?php
declare(strict_types=1);

namespace EonX\EasyRandom\Tests\Generators;

use EonX\EasyRandom\Generators\RandomGenerator;
use EonX\EasyRandom\Generators\UuidGenerator;
use EonX\EasyRandom\Interfaces\RandomIntegerGeneratorInterface;
use EonX\EasyRandom\Interfaces\RandomStringGeneratorInterface;
use EonX\EasyRandom\Interfaces\RandomStringInterface;
use EonX\EasyRandom\Interfaces\UuidGeneratorInterface;
use EonX\EasyRandom\Tests\AbstractTestCase;
use EonX\EasyRandom\ValueObject\RandomString;
use Symfony\Component\Uid\Factory\UuidFactory;

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
            uuidGenerator: new UuidGenerator(new UuidFactory()),
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
            uuidGenerator: new UuidGenerator(new UuidFactory()),
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
