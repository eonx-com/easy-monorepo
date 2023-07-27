<?php

declare(strict_types=1);

namespace EonX\EasyRandom\Tests\Generators;

use EonX\EasyRandom\Constraints\CallbackRandomStringConstraint;
use EonX\EasyRandom\Exceptions\InvalidAlphabetException;
use EonX\EasyRandom\Exceptions\InvalidAlphabetNameException;
use EonX\EasyRandom\Exceptions\InvalidRandomStringException;
use EonX\EasyRandom\Generators\RandomStringGenerator;
use EonX\EasyRandom\Interfaces\RandomStringInterface;
use EonX\EasyRandom\Tests\AbstractTestCase;
use EonX\EasyRandom\Tests\Stubs\AlwaysValidRandomStringConstraintStub;
use Symfony\Component\String\UnicodeString;

final class RandomStringGeneratorTest extends AbstractTestCase
{
    /**
     * @see testRandomString
     */
    public static function providerTestRandomString(): iterable
    {
        yield 'Default configs' => [
            'configure' => static function (RandomStringInterface $randomString): void {
                // No body needed
            },
            'assert' => static function (string $randomString): void {
                // No body needed
            },
            'length' => null,
        ];

        foreach (RandomStringInterface::ALPHABET_NAMES as $name) {
            yield \sprintf('Exclude %s', $name) => [
                'configure' => static function (RandomStringInterface $randomString) use ($name): void {
                    $randomString->{\sprintf('exclude%s', \ucfirst($name))}();
                },
                'assert' => static function (string $randomString) use ($name): void {
                    self::assertAlphabetExcluded($name, $randomString);
                },
                'length' => null,
            ];

            yield \sprintf('Include only %s', $name) => [
                'configure' => static function (RandomStringInterface $randomString) use ($name): void {
                    $randomString
                        ->clear()
                        ->{\sprintf('include%s', \ucfirst($name))}();
                },
                'assert' => static function (string $randomString) use ($name): void {
                    self::assertIncludesOnly(RandomStringInterface::ALPHABETS[$name], $randomString);
                },
                'length' => null,
            ];
        }

        yield 'Override alphabet' => [
            'configure' => static function (RandomStringInterface $randomString): void {
                $randomString->alphabet('EONX');
            },
            'assert' => static function (string $randomString): void {
                self::assertIncludesOnly('EONX', $randomString);
            },
            'length' => null,
        ];

        yield 'User friendly' => [
            'configure' => static function (RandomStringInterface $randomString): void {
                $randomString
                    ->constraints([new AlwaysValidRandomStringConstraintStub()])
                    ->maxAttempts(10)
                    ->userFriendly();
            },
            'assert' => static function (string $randomString): void {
                self::assertAlphabetExcluded(RandomStringInterface::AMBIGUOUS, $randomString);
                self::assertAlphabetExcluded(RandomStringInterface::LOWERCASE, $randomString);
                self::assertAlphabetExcluded(RandomStringInterface::SYMBOL, $randomString);
                self::assertAlphabetExcluded(RandomStringInterface::SIMILAR, $randomString);
                self::assertAlphabetExcluded(RandomStringInterface::VOWEL, $randomString);
            },
            'length' => null,
        ];

        yield 'Prefix respect length' => [
            'configure' => static function (RandomStringInterface $randomString): void {
                $randomString
                    ->prefix('eonx_')
                    ->userFriendly();
            },
            'assert' => static function (string $randomString): void {
                self::assertSame(16, (new UnicodeString($randomString))->length());
            },
            'length' => 16,
        ];

        yield 'Suffix respect length' => [
            'configure' => static function (RandomStringInterface $randomString): void {
                $randomString
                    ->suffix('_eonx')
                    ->userFriendly();
            },
            'assert' => static function (string $randomString): void {
                self::assertSame(16, (new UnicodeString($randomString))->length());
            },
            'length' => 16,
        ];

        yield 'Prefix and Suffix respect length' => [
            'configure' => static function (RandomStringInterface $randomString): void {
                $randomString
                    ->prefix('eonx_')
                    ->suffix('_eonx')
                    ->userFriendly();
            },
            'assert' => static function (string $randomString): void {
                self::assertSame(16, (new UnicodeString($randomString))->length());
            },
            'length' => 16,
        ];
    }

    public function testInvalidAlphabetExceptionThrown(): void
    {
        $this->expectException(InvalidAlphabetException::class);
        $sut = (new RandomStringGenerator())
            ->generate(8)
            ->alphabet('');

        $sut->__toString();
    }

    public function testInvalidAlphabetNameExceptionThrown(): void
    {
        $this->expectException(InvalidAlphabetNameException::class);
        $sut = (new RandomStringGenerator())
            ->generate(8)
            ->exclude('invalid');

        $sut->__toString();
    }

    public function testInvalidRandomStringExceptionThrown(): void
    {
        $this->expectException(InvalidRandomStringException::class);
        $alwaysInvalid = new CallbackRandomStringConstraint(static fn (): bool => false);
        $sut = (new RandomStringGenerator())
            ->generate(8)
            ->constraints([$alwaysInvalid]);

        $sut->__toString();
    }

    /**
     * @dataProvider providerTestRandomString
     */
    public function testRandomString(callable $configure, callable $assert, ?int $length = null): void
    {
        $length ??= 100;
        $sut = new RandomStringGenerator();

        $randomString1 = $sut->generate($length);
        $randomString2 = $sut->generate($length);
        $configure($randomString1);
        $configure($randomString2);
        $result1 = (string)$randomString1;
        $result2 = (string)$randomString2;

        self::assertSame($result1, $randomString1->__toString());
        self::assertSame($result2, $randomString2->__toString());
        self::assertSame($length, \mb_strlen($result1));
        self::assertSame($length, \mb_strlen($result2));
        self::assertNotSame($result1, $result2);
        $assert($result1);
        $assert($result2);
    }

    private static function assertAlphabetExcluded(string $alphabetName, string $randomString): void
    {
        foreach (\str_split(RandomStringInterface::ALPHABETS[$alphabetName]) as $char) {
            self::assertFalse(\str_contains($randomString, $char));
        }
    }

    private static function assertIncludesOnly(string $alphabet, string $randomString): void
    {
        $alphabet = \preg_quote($alphabet, '#');

        self::assertSame(0, \preg_match(\sprintf('#[^%s]#', $alphabet), $randomString));
    }
}
