<?php
declare(strict_types=1);

namespace EonX\EasyRandom\Interfaces;

use Stringable;

interface RandomStringInterface extends Stringable
{
    public const ALPHABETS = [
        self::AMBIGUOUS => '-[]\\;\',./!()_{}:"<>?',
        self::LOWERCASE => 'abcdefghijklmnopqrstuvwxyz',
        self::NUMERIC => '0123456789',
        self::SIMILAR => 'iIlLoOqQsS015!$',
        self::SYMBOL => '-=[]\\;\',./~!@#$%^&*()_+{}|:"<>?',
        self::UPPERCASE => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
        self::VOWEL => 'aAeEiIoOuU',
    ];

    public const ALPHABET_NAMES = [
        self::AMBIGUOUS,
        self::LOWERCASE,
        self::NUMERIC,
        self::SIMILAR,
        self::SYMBOL,
        self::UPPERCASE,
        self::VOWEL,
    ];

    public const AMBIGUOUS = 'ambiguous';

    public const LOWERCASE = 'lowercase';

    public const NUMERIC = 'numeric';

    public const SIMILAR = 'similar';

    public const SYMBOL = 'symbol';

    public const UPPERCASE = 'uppercase';

    public const VOWEL = 'vowel';

    public function __toString(): string;

    public function alphabet(string $alphabet): self;

    public function clear(): self;

    /**
     * @param \EonX\EasyRandom\Interfaces\RandomStringConstraintInterface[] $constraints
     */
    public function constraints(array $constraints): self;

    public function exclude(string $alphabetName): self;

    public function excludeAmbiguous(): self;

    public function excludeLowercase(): self;

    public function excludeNumeric(): self;

    public function excludeSimilar(): self;

    public function excludeSymbol(): self;

    public function excludeUppercase(): self;

    public function excludeVowel(): self;

    public function include(string $alphabetName): self;

    public function includeAmbiguous(): self;

    public function includeLowercase(): self;

    public function includeNumeric(): self;

    public function includeSimilar(): self;

    public function includeSymbol(): self;

    public function includeUppercase(): self;

    public function includeVowel(): self;

    public function maxAttempts(int $maxAttempts): self;

    public function prefix(string $prefix): self;

    public function suffix(string $suffix): self;

    public function userFriendly(): self;
}
