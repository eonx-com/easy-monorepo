<?php

declare(strict_types=1);

namespace EonX\EasyRandom\Interfaces;

interface RandomStringInterface
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
        self::VOWEL
    ];

    public const AMBIGUOUS = 'ambiguous';
    public const LOWERCASE = 'lowercase';
    public const NUMERIC = 'numeric';
    public const SIMILAR = 'similar';
    public const SYMBOL = 'symbol';
    public const UPPERCASE = 'uppercase';
    public const VOWEL = 'vowel';

    public function __toString(): string;

    /**
     * @param \EonX\EasyRandom\Interfaces\RandomStringConstraintInterface[] $constraints
     */
    public function constraints(array $constraints): self;

    public function exclude(string $alphabet): self;

    public function include(string $alphabet): self;

    public function maxAttempts(int $maxAttempts): self;

    public function userFriendly(): self;
}
