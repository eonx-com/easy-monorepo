<?php
declare(strict_types=1);

namespace EonX\EasyRandom\Tests\Unit\Generator;

use EonX\EasyRandom\Constraint\CallbackRandomStringConstraint;
use EonX\EasyRandom\Enum\Alphabet;
use EonX\EasyRandom\Exception\InvalidAlphabetException;
use EonX\EasyRandom\Exception\InvalidRandomStringException;
use EonX\EasyRandom\Generator\RandomStringGenerator;
use EonX\EasyRandom\Tests\Stub\Constraint\AlwaysValidRandomStringConstraintStub;
use EonX\EasyRandom\Tests\Unit\AbstractUnitTestCase;
use EonX\EasyRandom\ValueObject\RandomStringInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\String\UnicodeString;

final class RandomStringGeneratorTest extends AbstractUnitTestCase
{
    /**
     * @see testRandomString
     */
    public static function provideRandomStringData(): iterable
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

        foreach (Alphabet::cases() as $alphabet) {
            yield \sprintf('Exclude %s', $alphabet->name) => [
                'configure' => static function (RandomStringInterface $randomString) use ($alphabet): void {
                    $randomString->{\sprintf('exclude%s', $alphabet->name)}();
                },
                'assert' => static function (string $randomString) use ($alphabet): void {
                    self::assertAlphabetExcluded($alphabet, $randomString);
                },
                'length' => null,
            ];

            yield \sprintf('Include only %s', $alphabet->name) => [
                'configure' => static function (RandomStringInterface $randomString) use ($alphabet): void {
                    $randomString
                        ->clear()
                        ->{\sprintf('include%s', $alphabet->name)}();
                },
                'assert' => static function (string $randomString) use ($alphabet): void {
                    self::assertIncludesOnly($alphabet, $randomString);
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
                self::assertAlphabetExcluded(Alphabet::Ambiguous, $randomString);
                self::assertAlphabetExcluded(Alphabet::Lowercase, $randomString);
                self::assertAlphabetExcluded(Alphabet::Symbol, $randomString);
                self::assertAlphabetExcluded(Alphabet::Similar, $randomString);
                self::assertAlphabetExcluded(Alphabet::Vowel, $randomString);
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

    public function testInvalidRandomStringExceptionThrown(): void
    {
        $this->expectException(InvalidRandomStringException::class);
        $alwaysInvalid = new CallbackRandomStringConstraint(static fn (): bool => false);
        $sut = (new RandomStringGenerator())
            ->generate(8)
            ->constraints([$alwaysInvalid]);

        $sut->__toString();
    }

    #[DataProvider('provideRandomStringData')]
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

    private static function assertAlphabetExcluded(Alphabet $alphabet, string $randomString): void
    {
        foreach (\str_split($alphabet->value) as $char) {
            self::assertFalse(\str_contains($randomString, $char));
        }
    }

    private static function assertIncludesOnly(Alphabet|string $alphabet, string $randomString): void
    {
        $alphabet = \preg_quote(\is_string($alphabet) ? $alphabet : $alphabet->value, '#');

        self::assertDoesNotMatchRegularExpression(\sprintf('#[^%s]#', $alphabet), $randomString);
    }
}
