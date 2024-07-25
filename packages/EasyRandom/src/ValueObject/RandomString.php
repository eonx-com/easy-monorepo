<?php
declare(strict_types=1);

namespace EonX\EasyRandom\ValueObject;

use EonX\EasyRandom\Enum\Alphabet;
use EonX\EasyRandom\Exception\InvalidAlphabetException;
use EonX\EasyRandom\Exception\InvalidRandomStringException;
use SplObjectStorage;
use Symfony\Component\String\ByteString;
use Symfony\Component\String\UnicodeString;

final class RandomString implements RandomStringInterface
{
    /**
     * @var \EonX\EasyRandom\Constraint\RandomStringConstraintInterface[]|null
     */
    private ?array $constraints = [];

    private SplObjectStorage $excludes;

    private SplObjectStorage $includes;

    private int $maxAttempts = 100;

    private ?string $overrideAlphabet = null;

    private ?string $prefix = null;

    private ?string $randomString = null;

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

    public function __toString(): string
    {
        return $this->randomString();
    }

    public function alphabet(string $alphabet): RandomStringInterface
    {
        $this->overrideAlphabet = $this->validateAlphabet($alphabet);

        return $this;
    }

    public function clear(): RandomStringInterface
    {
        $this->includes = new SplObjectStorage();
        $this->excludes = new SplObjectStorage();

        return $this;
    }

    /**
     * @param \EonX\EasyRandom\Constraint\RandomStringConstraintInterface[] $constraints
     */
    public function constraints(array $constraints): RandomStringInterface
    {
        $this->constraints = $constraints;

        return $this;
    }

    public function exclude(Alphabet $alphabet): RandomStringInterface
    {
        $this->excludes->attach($alphabet);

        return $this;
    }

    public function excludeAmbiguous(): RandomStringInterface
    {
        $this->exclude(Alphabet::Ambiguous);

        return $this;
    }

    public function excludeLowercase(): RandomStringInterface
    {
        $this->exclude(Alphabet::Lowercase);

        return $this;
    }

    public function excludeNumeric(): RandomStringInterface
    {
        $this->exclude(Alphabet::Numeric);

        return $this;
    }

    public function excludeSimilar(): RandomStringInterface
    {
        $this->exclude(Alphabet::Similar);

        return $this;
    }

    public function excludeSymbol(): RandomStringInterface
    {
        $this->exclude(Alphabet::Symbol);

        return $this;
    }

    public function excludeUppercase(): RandomStringInterface
    {
        $this->exclude(Alphabet::Uppercase);

        return $this;
    }

    public function excludeVowel(): RandomStringInterface
    {
        $this->exclude(Alphabet::Vowel);

        return $this;
    }

    public function include(Alphabet $alphabet): RandomStringInterface
    {
        $this->includes->attach($alphabet);
        $this->excludes->detach($alphabet);

        return $this;
    }

    public function includeAmbiguous(): RandomStringInterface
    {
        $this->include(Alphabet::Ambiguous);

        return $this;
    }

    public function includeLowercase(): RandomStringInterface
    {
        $this->include(Alphabet::Lowercase);

        return $this;
    }

    public function includeNumeric(): RandomStringInterface
    {
        $this->include(Alphabet::Numeric);

        return $this;
    }

    public function includeSimilar(): RandomStringInterface
    {
        $this->include(Alphabet::Similar);

        return $this;
    }

    public function includeSymbol(): RandomStringInterface
    {
        $this->include(Alphabet::Symbol);

        return $this;
    }

    public function includeUppercase(): RandomStringInterface
    {
        $this->include(Alphabet::Uppercase);

        return $this;
    }

    public function includeVowel(): RandomStringInterface
    {
        $this->include(Alphabet::Vowel);

        return $this;
    }

    public function maxAttempts(int $maxAttempts): RandomStringInterface
    {
        $this->maxAttempts = $maxAttempts;

        return $this;
    }

    public function prefix(string $prefix): RandomStringInterface
    {
        $this->prefix = $prefix;

        return $this;
    }

    public function suffix(string $suffix): RandomStringInterface
    {
        $this->suffix = $suffix;

        return $this;
    }

    public function userFriendly(): RandomStringInterface
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

    private function randomString(): string
    {
        if ($this->randomString !== null) {
            return $this->randomString;
        }

        $attempts = 0;

        do {
            $randomString = ByteString::fromRandom($this->resolveLength(), $this->resolveAlphabet())->toString();
            $attempts++;
        } while ($this->validateString($randomString) === false && $attempts < $this->maxAttempts);

        if ($attempts === $this->maxAttempts) {
            throw new InvalidRandomStringException(\sprintf(
                'Could not generate valid random string for alphabet "%s"',
                $this->resolveAlphabet()
            ));
        }

        if ($this->prefix !== null) {
            $randomString = $this->prefix . $randomString;
        }

        if ($this->suffix !== null) {
            $randomString .= $this->suffix;
        }

        $this->randomString = $randomString;

        return $this->randomString;
    }

    private function resolveAlphabet(): string
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

    private function resolveLength(): int
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

    private function validateAlphabet(string $alphabet): string
    {
        if ($alphabet !== '') {
            return $alphabet;
        }

        throw new InvalidAlphabetException('Alphabet to generate random string cannot be empty');
    }

    private function validateString(string $randomString): bool
    {
        if ($this->constraints === null) {
            return true;
        }

        foreach ($this->constraints as $constraint) {
            if ($constraint->isValid($randomString) === false) {
                return false;
            }
        }

        return true;
    }
}
