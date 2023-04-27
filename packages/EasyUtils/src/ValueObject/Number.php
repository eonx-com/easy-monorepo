<?php

declare(strict_types=1);

namespace EonX\EasyUtils\ValueObject;

use Stringable;
use UnexpectedValueException;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods) We need many public methods
 */
final class Number implements Stringable
{
    private const DEFAULT_PRECISION = 0;

    private const SCALE = 99;

    private int $precision;

    private string $value;

    public function __construct(int|string|self $value, ?int $precision = null)
    {
        if (\is_string($value)) {
            $value = \trim($value, "\s");
        }
        if ($value instanceof self === false && \is_numeric($value) === false) {
            throw new UnexpectedValueException("Value must be a number but '{$value}' is given.");
        }
        $this->value = (string)$value;
        $this->precision = $precision ?? self::DEFAULT_PRECISION;
    }

    public static function max(?self ...$values): ?self
    {
        return \array_reduce(
            $values,
            static function (?self $leftOperand = null, ?self $rightOperand = null) {
                if ($leftOperand === null) {
                    return $rightOperand;
                }
                if ($rightOperand === null) {
                    return $leftOperand;
                }

                return $leftOperand->isGreaterThan($rightOperand) ? $leftOperand : $rightOperand;
            }
        );
    }

    public static function min(?self ...$values): ?self
    {
        return \array_reduce(
            $values,
            static function (?self $leftOperand = null, ?self $rightOperand = null) {
                if ($leftOperand === null) {
                    return $rightOperand;
                }
                if ($rightOperand === null) {
                    return $leftOperand;
                }

                return $leftOperand->isLessThan($rightOperand) ? $leftOperand : $rightOperand;
            }
        );
    }

    public function __toString(): string
    {
        return $this->round($this->value);
    }

    public function abs(): self
    {
        return new self(\ltrim($this->value, '-'), $this->precision);
    }

    public function add(int|string|float|self $addition): self
    {
        $additionValue = $addition instanceof self ? $addition->value : (string)$addition;
        $sum = $this->round(\bcadd($this->value, $additionValue, self::SCALE));

        return new self($sum, $this->precision);
    }

    /**
     * Returns 0 if the two values are equal, 1 if this value is larger than the $operand value, -1 otherwise.
     */
    public function compare(int|string|float|self $operand): int
    {
        $operandValue = $operand instanceof self ? $operand->value : (string)$operand;

        return \bccomp($this->value, $operandValue, self::SCALE);
    }

    public function divide(int|string|float|self $divisor): self
    {
        $quotient = $this->round(\bcdiv($this->value, (string)$divisor, self::SCALE));

        return new self($quotient, $this->precision);
    }

    public function isEqualTo(int|string|float|self $operand): bool
    {
        return $this->compare($operand) === 0;
    }

    public function isGreaterThan(int|string|float|self $operand): bool
    {
        return $this->compare($operand) === 1;
    }

    public function isGreaterThanOrEqualTo(int|string|float|self $operand): bool
    {
        return $this->compare($operand) >= 0;
    }

    public function isLessThan(int|string|float|self $operand): bool
    {
        return $this->compare($operand) === -1;
    }

    public function isLessThanOrEqualTo(int|string|float|self $operand): bool
    {
        return $this->compare($operand) <= 0;
    }

    public function isNegative(): bool
    {
        return $this->isLessThan(0);
    }

    public function isNegativeOrZero(): bool
    {
        return $this->isLessThanOrEqualTo(0);
    }

    public function isPositive(): bool
    {
        return $this->isGreaterThan(0);
    }

    public function isPositiveOrZero(): bool
    {
        return $this->isGreaterThanOrEqualTo(0);
    }

    public function isZero(): bool
    {
        return $this->isEqualTo(0);
    }

    public function multiply(int|string|float|self $multiplier): self
    {
        $product = $this->round(\bcmul($this->value, (string)$multiplier, self::SCALE));

        return new self($product, $this->precision);
    }

    public function subtract(int|string|float|self $subtrahend): self
    {
        $difference = $this->round(\bcsub($this->value, (string)$subtrahend, self::SCALE));

        return new self($difference, $this->precision);
    }

    public function toMoneyString(): string
    {
        $value = (string)(new self($this->value, 2))->divide(100);

        return \str_ends_with($value, '.00') ? (string)(int)$value : $value;
    }

    /**
     * @see https://stackoverflow.com/a/1653826/430062
     */
    private function round(string $value): string
    {
        if (\str_contains($value, '.') === false) {
            return \bcadd($value, '0', $this->precision);
        }

        $floatPartLength = \strlen($value) - \strpos($value, '.') - 1;
        if ($floatPartLength <= $this->precision) {
            return $value . \str_repeat('0', $this->precision - $floatPartLength);
        }

        $delta = '0.' . \str_repeat('0', $this->precision) . '5';

        $lastDigit = (int)$value[\strlen($value) - 1];
        if ($lastDigit === 5) {
            $previousDigit = $value[\strlen($value) - 2] === '.'
                ? $value[\strlen($value) - 3]
                : $value[\strlen($value) - 2];
            $isEven = ($previousDigit % 2 === 0);
            $delta = $isEven ? '-' . $delta : $delta;
        }

        if ($value[0] !== '-') {
            return \bcadd($value, $delta, $this->precision);
        }

        return \bcsub($value, $delta, $this->precision);
    }
}
