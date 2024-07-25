<?php
declare(strict_types=1);

namespace EonX\EasyRandom\ValueObject;

use EonX\EasyRandom\Enum\Alphabet;
use Stringable;

interface RandomStringInterface extends Stringable
{
    public function __toString(): string;

    public function alphabet(string $alphabet): self;

    public function clear(): self;

    /**
     * @param \EonX\EasyRandom\Constraint\RandomStringConstraintInterface[] $constraints
     */
    public function constraints(array $constraints): self;

    public function exclude(Alphabet $alphabet): self;

    public function excludeAmbiguous(): self;

    public function excludeLowercase(): self;

    public function excludeNumeric(): self;

    public function excludeSimilar(): self;

    public function excludeSymbol(): self;

    public function excludeUppercase(): self;

    public function excludeVowel(): self;

    public function include(Alphabet $alphabet): self;

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
