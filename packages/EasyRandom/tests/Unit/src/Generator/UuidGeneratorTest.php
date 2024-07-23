<?php
declare(strict_types=1);

namespace EonX\EasyRandom\Tests\Unit\Generator;

use EonX\EasyRandom\Generator\RandomGenerator;
use EonX\EasyRandom\Generator\RandomIntegerGenerator;
use EonX\EasyRandom\Generator\RandomIntegerGeneratorInterface;
use EonX\EasyRandom\Generator\RandomStringGenerator;
use EonX\EasyRandom\Generator\RandomStringGeneratorInterface;
use EonX\EasyRandom\Generator\UuidGenerator;
use EonX\EasyRandom\Generator\UuidGeneratorInterface;
use EonX\EasyRandom\Tests\Unit\AbstractUnitTestCase;
use EonX\EasyRandom\ValueObject\RandomString;
use EonX\EasyRandom\ValueObject\RandomStringInterface;
use Symfony\Component\Uid\Factory\UuidFactory;

final class UuidGeneratorTest extends AbstractUnitTestCase
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
            randomIntegerGenerator: $randomIntegerGenerator,
            randomStringGenerator: new RandomStringGenerator()
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
            randomIntegerGenerator: new RandomIntegerGenerator(),
            randomStringGenerator: $randomStringGenerator,
        );

        $result = $sut->string(100);

        self::assertEquals(new RandomString(100), $result);
    }

    public function testUuidSucceedsWithCustomUuidGenerator(): void
    {
        $uuidGenerator = new class() implements UuidGeneratorInterface {
            public function generate(): string
            {
                return 'some-uuid';
            }
        };
        $sut = new RandomGenerator(
            uuidGenerator: $uuidGenerator,
            randomIntegerGenerator: new RandomIntegerGenerator(),
            randomStringGenerator: new RandomStringGenerator()
        );

        $result = $sut->uuid();

        self::assertSame('some-uuid', $result);
    }
}
