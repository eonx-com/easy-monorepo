<?php

declare(strict_types=1);

namespace EonX\EasyRandom;

use EonX\EasyRandom\Exceptions\InvalidRandomStringException;
use EonX\EasyRandom\Interfaces\RandomStringInterface;
use Symfony\Component\String\ByteString;

final class RandomString implements RandomStringInterface
{
    /**
     * @var \EonX\EasyRandom\Interfaces\RandomStringConstraintInterface[]
     */
    private $constraints;

    /**
     * @var bool[]
     */
    private $excludes = [];

    /**
     * @var bool[]
     */
    private $includes = [];

    /**
     * @var int
     */
    private $length;

    /**
     * @var int
     */
    private $maxAttempts = 100;

    /**
     * @var string
     */
    private $randomString;

    /**
     * @var string
     */
    private $resolvedAlphabet;

    public function __construct(int $length)
    {
        $this->length = $length;

        foreach (self::ALPHABET_NAMES as $alphabet) {
            $this->includes[$alphabet] = true;
        }
    }

    public function __toString(): string
    {
        return $this->randomString();
    }

    /**
     * @param \EonX\EasyRandom\Interfaces\RandomStringConstraintInterface[] $constraints
     */
    public function constraints(array $constraints): RandomStringInterface
    {
        $this->constraints = $constraints;

        return $this;
    }

    public function exclude(string $alphabet): RandomStringInterface
    {
        $this->excludes[$alphabet] = true;

        return $this;
    }

    public function include(string $alphabet): RandomStringInterface
    {
        $this->includes[$alphabet] = true;

        return $this;
    }

    public function maxAttempts(int $maxAttempts): RandomStringInterface
    {
        $this->maxAttempts = $maxAttempts;

        return $this;
    }

    public function userFriendly(): RandomStringInterface
    {
        $this
            ->include(self::NUMERIC)
            ->include(self::UPPERCASE)
            ->exclude(self::AMBIGUOUS)
            ->exclude(self::LOWERCASE)
            ->exclude(self::SYMBOL)
            ->exclude(self::SIMILAR)
            ->exclude(self::VOWEL); // Pretty useful to avoid "bad words" in generated strings

        return $this;
    }

    private function randomString(): string
    {
        if ($this->randomString !== null) {
            return $this->randomString;
        }

        $attempts = 0;

        do {
            $randomString = ByteString::fromRandom($this->length, $this->resolveAlphabet())->toString();
            $attempts++;
        } while ($this->validateString($randomString) === false && $attempts < $this->maxAttempts);

        if ($attempts === $this->maxAttempts) {
            throw new InvalidRandomStringException(\sprintf(
                'Could not generate valid random string for alphabet "%s"',
                $this->resolveAlphabet()
            ));
        }

        return $this->randomString = $randomString;
    }

    private function resolveAlphabet(): string
    {
        if ($this->resolvedAlphabet !== null) {
            return $this->resolvedAlphabet;
        }

        $currentAlphabet = [];

        // Includes
        foreach (\array_keys($this->includes) as $alphabet) {
            foreach (\str_split(self::ALPHABETS[$alphabet]) as $char) {
                $currentAlphabet[] = $char;
            }
        }

        // Excludes
        foreach (\array_keys($this->excludes) as $alphabet) {
            $currentAlphabet = \array_diff($currentAlphabet, \str_split(self::ALPHABETS[$alphabet]));
        }

        return $this->resolvedAlphabet = \implode('', $currentAlphabet);
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
