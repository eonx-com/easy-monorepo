<?php
declare(strict_types=1);

namespace EonX\EasyRandom\Tests\Unit\Generator;

use EonX\EasyRandom\Constraint\CallbackRandomStringConstraint;
use EonX\EasyRandom\Enum\Alphabet;
use EonX\EasyRandom\Exception\InvalidRandomStringException;
use EonX\EasyRandom\Generator\RandomStringGenerator;
use EonX\EasyRandom\Tests\Stub\Constraint\AlwaysValidRandomStringConstraintStub;
use EonX\EasyRandom\Tests\Unit\AbstractUnitTestCase;
use EonX\EasyRandom\ValueObject\RandomStringConfig;
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
            'randomStringConfig' => new RandomStringConfig(100),
            'assert' => static function (string $randomString): void {
                // No body needed
            },
        ];

        foreach (Alphabet::cases() as $alphabet) {
            $alphabetMethod = \sprintf('exclude%s', $alphabet->name);
            yield \sprintf('Exclude %s', $alphabet->name) => [
                'randomStringConfig' => new RandomStringConfig(100)
                    ->$alphabetMethod(),
                'assert' => static function (string $randomString) use ($alphabet): void {
                    self::assertAlphabetExcluded($alphabet, $randomString);
                },
            ];

            $alphabetMethod = \sprintf('include%s', $alphabet->name);
            yield \sprintf('Include only %s', $alphabet->name) => [
                'randomStringConfig' => new RandomStringConfig(100)
                    ->clear()
                    ->$alphabetMethod(),
                'assert' => static function (string $randomString) use ($alphabet): void {
                    self::assertIncludesOnly($alphabet, $randomString);
                },
            ];
        }

        yield 'Override alphabet' => [
            'randomStringConfig' => new RandomStringConfig(100)
                ->alphabet('EONX'),
            'assert' => static function (string $randomString): void {
                self::assertIncludesOnly('EONX', $randomString);
            },
        ];

        yield 'User friendly' => [
            'randomStringConfig' => new RandomStringConfig(100)
                ->constraints([new AlwaysValidRandomStringConstraintStub()])
                ->maxAttempts(10)
                ->userFriendly(),
            'assert' => static function (string $randomString): void {
                self::assertAlphabetExcluded(Alphabet::Ambiguous, $randomString);
                self::assertAlphabetExcluded(Alphabet::Lowercase, $randomString);
                self::assertAlphabetExcluded(Alphabet::Symbol, $randomString);
                self::assertAlphabetExcluded(Alphabet::Similar, $randomString);
                self::assertAlphabetExcluded(Alphabet::Vowel, $randomString);
            },
        ];

        yield 'Prefix respect length' => [
            'randomStringConfig' => new RandomStringConfig(16)
                ->prefix('eonx_')
                ->userFriendly(),
            'assert' => static function (string $randomString): void {
                self::assertSame(16, new UnicodeString($randomString)->length());
            },
        ];

        yield 'Suffix respect length' => [
            'randomStringConfig' => new RandomStringConfig(16)
                ->suffix('_eonx')
                ->userFriendly(),
            'assert' => static function (string $randomString): void {
                self::assertSame(16, new UnicodeString($randomString)->length());
            },
        ];

        yield 'Prefix and Suffix respect length' => [
            'randomStringConfig' => new RandomStringConfig(16)
                ->prefix('eonx_')
                ->suffix('_eonx')
                ->userFriendly(),
            'assert' => static function (string $randomString): void {
                self::assertSame(16, new UnicodeString($randomString)->length());
            },
        ];
    }

    public function testInvalidRandomStringExceptionThrown(): void
    {
        $this->expectException(InvalidRandomStringException::class);
        $alwaysInvalid = new CallbackRandomStringConstraint(static fn (): bool => false);
        $randomStringConfig = new RandomStringConfig(8)
            ->constraints([$alwaysInvalid]);

        new RandomStringGenerator()
            ->generate($randomStringConfig);
    }

    #[DataProvider('provideRandomStringData')]
    public function testRandomString(RandomStringConfig $randomStringConfig, callable $assert): void
    {
        $sut = new RandomStringGenerator();
        $length = self::getPrivatePropertyValue($randomStringConfig, 'length');

        $randomString1 = $sut->generate($randomStringConfig);
        $randomString2 = $sut->generate($randomStringConfig);

        self::assertSame($length, \mb_strlen($randomString1));
        self::assertSame($length, \mb_strlen($randomString2));
        self::assertNotSame($randomString1, $randomString2);
        $assert($randomString1);
        $assert($randomString2);
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
