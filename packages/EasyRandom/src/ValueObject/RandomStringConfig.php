<?php
declare(strict_types=1);

namespace EonX\EasyRandom\ValueObject;

use EonX\EasyRandom\Enum\Alphabet;
use EonX\EasyRandom\Exception\InvalidAlphabetException;
use SplObjectStorage;
use Symfony\Component\String\UnicodeString;

final class RandomStringConfig
{
    /**
     * @var \EonX\EasyRandom\Constraint\RandomStringConstraintInterface[]|null
     */
    private ?array $constraints = [];

    /**
     * @var \SplObjectStorage<\EonX\EasyRandom\Enum\Alphabet, null>
     */
    private SplObjectStorage $excludes;

    /**
     * @var \SplObjectStorage<\EonX\EasyRandom\Enum\Alphabet, null>
     */
    private SplObjectStorage $includes;

    private int $maxAttempts = 100;

    private ?string $overrideAlphabet = null;

    private ?string $prefix = null;

    private ?string $resolvedAlphabet = null;

    private ?string $suffix = null;

    public function __construct(
        private int $length,
    ) {
        $this->includes = new SplObjectStorage();
        $this->excludes = new SplObjectStorage();

        foreach (Alphabet::cases() as $alphabet) {
            $this->include($alphabet);
        }
    }

    public function alphabet(string $alphabet): self
    {
        $this->overrideAlphabet = $this->validateAlphabet($alphabet);

        return $this;
    }

    public function clear(): self
    {
        $this->includes = new SplObjectStorage();
        $this->excludes = new SplObjectStorage();

        return $this;
    }

    /**
     * @param \EonX\EasyRandom\Constraint\RandomStringConstraintInterface[] $constraints
     */
    public function constraints(array $constraints): self
    {
        $this->constraints = $constraints;

        return $this;
    }

    public function exclude(Alphabet $alphabet): self
    {
        $this->excludes->attach($alphabet);

        return $this;
    }

    public function excludeAmbiguous(): self
    {
        $this->exclude(Alphabet::Ambiguous);

        return $this;
    }

    public function excludeLowercase(): self
    {
        $this->exclude(Alphabet::Lowercase);

        return $this;
    }

    public function excludeNumeric(): self
    {
        $this->exclude(Alphabet::Numeric);

        return $this;
    }

    public function excludeSimilar(): self
    {
        $this->exclude(Alphabet::Similar);

        return $this;
    }

    public function excludeSymbol(): self
    {
        $this->exclude(Alphabet::Symbol);

        return $this;
    }

    public function excludeUppercase(): self
    {
        $this->exclude(Alphabet::Uppercase);

        return $this;
    }

    public function excludeVowel(): self
    {
        $this->exclude(Alphabet::Vowel);

        return $this;
    }

    /**
     * @return \EonX\EasyRandom\Constraint\RandomStringConstraintInterface[]|null
     */
    public function getConstraints(): ?array
    {
        return $this->constraints;
    }

    public function getMaxAttempts(): int
    {
        return $this->maxAttempts;
    }

    public function getPrefix(): ?string
    {
        return $this->prefix;
    }

    public function getSuffix(): ?string
    {
        return $this->suffix;
    }

    public function include(Alphabet $alphabet): self
    {
        $this->includes->attach($alphabet);
        $this->excludes->detach($alphabet);

        return $this;
    }

    public function includeAmbiguous(): self
    {
        $this->include(Alphabet::Ambiguous);

        return $this;
    }

    public function includeLowercase(): self
    {
        $this->include(Alphabet::Lowercase);

        return $this;
    }

    public function includeNumeric(): self
    {
        $this->include(Alphabet::Numeric);

        return $this;
    }

    public function includeSimilar(): self
    {
        $this->include(Alphabet::Similar);

        return $this;
    }

    public function includeSymbol(): self
    {
        $this->include(Alphabet::Symbol);

        return $this;
    }

    public function includeUppercase(): self
    {
        $this->include(Alphabet::Uppercase);

        return $this;
    }

    public function includeVowel(): self
    {
        $this->include(Alphabet::Vowel);

        return $this;
    }

    public function maxAttempts(int $maxAttempts): self
    {
        $this->maxAttempts = $maxAttempts;

        return $this;
    }

    public function prefix(string $prefix): self
    {
        $this->prefix = $prefix;

        return $this;
    }

    public function resolveAlphabet(): string
    {
        if ($this->overrideAlphabet !== null) {
            return $this->overrideAlphabet;
        }

        if ($this->resolvedAlphabet !== null) {
            return $this->resolvedAlphabet;
        }

        $currentAlphabet = [];

        // Includes
        foreach ($this->includes as $alphabet) {
            foreach (\str_split($alphabet->value) as $char) {
                $currentAlphabet[] = $char;
            }
        }

        // Excludes
        foreach ($this->excludes as $alphabet) {
            $currentAlphabet = \array_diff($currentAlphabet, \str_split($alphabet->value));
        }

        $currentAlphabet = \array_unique($currentAlphabet);

        return $this->resolvedAlphabet = $this->validateAlphabet(\implode('', $currentAlphabet));
    }

    public function resolveLength(): int
    {
        $length = $this->length;

        foreach ([$this->prefix, $this->suffix] as $string) {
            if ($string !== null) {
                $length -= (new UnicodeString($string))
                    ->length();
            }
        }

        return $length;
    }

    public function suffix(string $suffix): self
    {
        $this->suffix = $suffix;

        return $this;
    }

    public function userFriendly(): self
    {
        $this
            ->include(Alphabet::Numeric)
            ->include(Alphabet::Uppercase)
            ->exclude(Alphabet::Ambiguous)
            ->exclude(Alphabet::Lowercase)
            ->exclude(Alphabet::Symbol)
            ->exclude(Alphabet::Similar)
            // Pretty useful to avoid "bad words" in generated strings
            ->exclude(Alphabet::Vowel);

        return $this;
    }

    private function validateAlphabet(string $alphabet): string
    {
        if ($alphabet !== '') {
            return $alphabet;
        }

        throw new InvalidAlphabetException('Alphabet to generate random string cannot be empty');
    }
}
