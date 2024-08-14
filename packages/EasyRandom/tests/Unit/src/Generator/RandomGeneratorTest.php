<?php
declare(strict_types=1);

namespace EonX\EasyRandom\Tests\Unit\Generator;

use EonX\EasyRandom\Generator\RandomGenerator;
use EonX\EasyRandom\Generator\RandomIntegerGeneratorInterface;
use EonX\EasyRandom\Generator\RandomStringGeneratorInterface;
use EonX\EasyRandom\Generator\UuidGenerator;
use EonX\EasyRandom\Generator\UuidGeneratorInterface;
use EonX\EasyRandom\Tests\Unit\AbstractUnitTestCase;
use EonX\EasyRandom\ValueObject\RandomStringConfig;
use Symfony\Component\Uid\Factory\UuidFactory;

final class RandomGeneratorTest extends AbstractUnitTestCase
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
            public function generate(RandomStringConfig $randomStringConfig): string
            {
                return 'some-random-string';
            }
        };
        $sut = new RandomGenerator(
            uuidGenerator: new UuidGenerator(new UuidFactory()),
            randomStringGenerator: $randomStringGenerator
        );

        $result = $sut->string(new RandomStringConfig(100));

        self::assertSame('some-random-string', $result);
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
