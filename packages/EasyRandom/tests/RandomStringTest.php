<?php

declare(strict_types=1);

namespace EonX\EasyRandom\Tests;

use EonX\EasyRandom\Exceptions\InvalidRandomStringException;
use EonX\EasyRandom\Interfaces\RandomStringInterface;
use EonX\EasyRandom\RandomGenerator;
use EonX\EasyRandom\Tests\Stubs\AlwaysInvalidRandomStringConstraintStub;
use EonX\EasyRandom\Tests\Stubs\AlwaysValidRandomStringConstraintStub;

final class RandomStringTest extends AbstractTestCase
{
    /**
     * @return iterable<mixed>
     */
    public function providerTestRandomString(): iterable
    {
        yield 'Default configs' => [];

        yield 'Exclude symbols' => [
            null,
            static function (RandomStringInterface $randomString): void {
                $randomString->exclude(RandomStringInterface::SYMBOL);
            },
            static function (string $randomString): void {
                self::assertAlphabetExcluded(RandomStringInterface::SYMBOL, $randomString);
            }
        ];

        yield 'Exclude vowels' => [
            null,
            static function (RandomStringInterface $randomString): void {
                $randomString->exclude(RandomStringInterface::VOWEL);
            },
            static function (string $randomString): void {
                self::assertAlphabetExcluded(RandomStringInterface::VOWEL, $randomString);
            }
        ];

        yield 'User friendly' => [
            null,
            static function (RandomStringInterface $randomString): void {
                $randomString
                    ->constraints([new AlwaysValidRandomStringConstraintStub()])
                    ->maxAttempts(10)
                    ->userFriendly();
            },
            static function (string $randomString): void {
                self::assertAlphabetExcluded(RandomStringInterface::AMBIGUOUS, $randomString);
                self::assertAlphabetExcluded(RandomStringInterface::LOWERCASE, $randomString);
                self::assertAlphabetExcluded(RandomStringInterface::SYMBOL, $randomString);
                self::assertAlphabetExcluded(RandomStringInterface::SIMILAR, $randomString);
                self::assertAlphabetExcluded(RandomStringInterface::VOWEL, $randomString);
            }
        ];
    }

    public function testInvalidRandomStringExceptionThrown(): void
    {
        $this->expectException(InvalidRandomStringException::class);

        (new RandomGenerator())
            ->randomString(8)
            ->constraints([new AlwaysInvalidRandomStringConstraintStub()])
            ->__toString();
    }

    /**
     * @dataProvider providerTestRandomString
     */
    public function testRandomString(?int $length = null, ?callable $configure = null, ?callable $assert = null): void
    {
        $iterations = $iterations ?? 100;
        $length = $length ?? 100;
        $generator = new RandomGenerator();
        $generated = [];

        for ($i = 0; $i < $iterations; $i++) {
            $randomString = $generator->randomString($length);

            if ($configure !== null) {
                $configure($randomString);
            }

            $randomStringToString = (string)$randomString;

            self::assertEquals($randomStringToString, $randomString->__toString());
            self::assertEquals($length, \strlen($randomStringToString));
            self::assertArrayNotHasKey($randomStringToString, $generated);

            if ($assert !== null) {
                $assert($randomStringToString);
            }

            $generated[$randomStringToString] = true;
        }
    }

    private static function assertAlphabetExcluded(string $alphabet, string $randomString): void
    {
        foreach (\str_split(RandomStringInterface::ALPHABETS[$alphabet]) as $char) {
            self::assertEquals(0, \strpos($randomString, $char));
        }
    }
}
