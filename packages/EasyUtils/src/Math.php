<?php

declare(strict_types=1);

namespace EonX\EasyUtils;

use EonX\EasyUtils\Exceptions\InvalidDivisionByZeroException;
use EonX\EasyUtils\Interfaces\MathComparisonInterface;
use EonX\EasyUtils\Interfaces\MathInterface;

final class Math implements MathInterface
{
    /**
     * @var string
     */
    private $decimalSeparator;

    /**
     * @var int
     */
    private $roundMode;

    /**
     * @var int
     */
    private $roundPrecision;

    /**
     * @var int
     */
    private $scale;

    /**
     * @var string
     */
    private $thousandsSeparator;

    public function __construct(
        ?int $roundPrecision = null,
        ?int $roundMode = null,
        ?int $scale = null,
        ?string $decimalSeparator = null,
        ?string $thousandsSeparator = null
    ) {
        $this->roundPrecision = $roundPrecision ?? self::ROUND_PRECISION;
        $this->roundMode = $roundMode ?? self::ROUND_MODE;
        $this->scale = $scale ?? self::SCALE;
        $this->decimalSeparator = $decimalSeparator ?? self::ROUND_DECIMAL_SEPARATOR;
        $this->thousandsSeparator = $thousandsSeparator ?? self::ROUND_THOUSANDS_SEPARATOR;
    }

    public function abs(string $value, ?int $precision = null, ?int $mode = null): string
    {
        return $this
            ->compareThat($value)
            ->lessThan('0')
            ? $this->multiply($value, '-1', $precision, $mode)
            : $this->round($value, $precision, $mode);
    }

    public function add(string $augend, string $addend, ?int $precision = null, ?int $mode = null): string
    {
        return $this->round(\bcadd($augend, $addend, $this->scale), $precision, $mode);
    }

    /**
     * @deprecated must be deleted on next major update, use compareThat() method instead
     */
    public function comp(string $leftOperand, string $rightOperand): int
    {
        return \bccomp($leftOperand, $rightOperand, $this->scale);
    }

    public function compareThat(string $leftOperand): MathComparisonInterface
    {
        return new MathComparison($leftOperand, $this->scale);
    }

    public function divide(string $dividend, string $divisor, ?int $precision = null, ?int $mode = null): string
    {
        $value = \bcdiv($dividend, $divisor, $this->scale);

        if ($value === null) {
            throw new InvalidDivisionByZeroException('Division by 0 is invalid');
        }

        return $this->round($value, $precision, $mode);
    }

    public function multiply(
        string $multiplicand,
        string $multiplier,
        ?int $precision = null,
        ?int $mode = null
    ): string {
        return $this->round(\bcmul($multiplicand, $multiplier, $this->scale), $precision, $mode);
    }

    public function round(string $value, ?int $precision = null, ?int $mode = null): string
    {
        $precision = $precision ?? $this->roundPrecision;
        $mode = $mode ?? $this->roundMode;

        $rounded = \round((float)$value, $precision, $mode);

        return \number_format($rounded, $precision, $this->decimalSeparator, $this->thousandsSeparator);
    }

    public function sub(string $minuend, string $subtrahend, ?int $precision = null, ?int $mode = null): string
    {
        return $this->round(\bcsub($minuend, $subtrahend, $this->scale), $precision, $mode);
    }
}
