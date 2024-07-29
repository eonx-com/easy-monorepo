<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Math\Helper;

use DivisionByZeroError;
use EonX\EasyUtils\Math\Exception\InvalidDivisionByZeroException;

final class MathHelper implements MathHelperInterface
{
    private const ROUND_DECIMAL_SEPARATOR = '.';

    private const ROUND_MODE = \PHP_ROUND_HALF_EVEN;

    private const ROUND_PRECISION = 0;

    private const ROUND_THOUSANDS_SEPARATOR = '';

    private const SCALE = 99;

    private readonly string $decimalSeparator;

    private readonly int $roundMode;

    private readonly int $roundPrecision;

    private readonly int $scale;

    private readonly string $thousandsSeparator;

    /**
     * @param int|null $roundMode [optional] <p>
     * One of PHP_ROUND_HALF_UP,
     * PHP_ROUND_HALF_DOWN,
     * PHP_ROUND_HALF_EVEN, or
     * PHP_ROUND_HALF_ODD.
     * </p>
     */
    public function __construct(
        ?int $roundPrecision = null,
        ?int $roundMode = null,
        ?int $scale = null,
        ?string $decimalSeparator = null,
        ?string $thousandsSeparator = null,
    ) {
        $this->roundPrecision = $roundPrecision ?? self::ROUND_PRECISION;
        $this->roundMode = $roundMode ?? self::ROUND_MODE;
        $this->scale = $scale ?? self::SCALE;
        $this->decimalSeparator = $decimalSeparator ?? self::ROUND_DECIMAL_SEPARATOR;
        $this->thousandsSeparator = $thousandsSeparator ?? self::ROUND_THOUSANDS_SEPARATOR;
    }

    public function abs(string $value, ?int $precision = null, ?int $mode = null): string
    {
        return $this->comp($value, '0') === -1
            ? $this->multiply($value, '-1', $precision, $mode)
            : $this->round($value, $precision, $mode);
    }

    public function add(string $augend, string $addend, ?int $precision = null, ?int $mode = null): string
    {
        return $this->round(\bcadd($augend, $addend, $this->scale), $precision, $mode);
    }

    public function comp(string $leftOperand, string $rightOperand): int
    {
        return \bccomp($leftOperand, $rightOperand, $this->scale);
    }

    public function compareThat(string $leftOperand): MathComparisonHelperInterface
    {
        return new MathComparisonHelper($leftOperand, (int)$this->scale);
    }

    public function divide(string $dividend, string $divisor, ?int $precision = null, ?int $mode = null): string
    {
        try {
            $value = \bcdiv($dividend, $divisor, $this->scale);
        } catch (DivisionByZeroError $exception) {
            throw new InvalidDivisionByZeroException('Division by 0 is invalid', 0, $exception);
        }

        return $this->round($value, $precision, $mode);
    }

    public function multiply(
        string $multiplicand,
        string $multiplier,
        ?int $precision = null,
        ?int $mode = null,
    ): string {
        return $this->round(\bcmul($multiplicand, $multiplier, $this->scale), $precision, $mode);
    }

    public function round(string $value, ?int $precision = null, ?int $mode = null): string
    {
        $precision ??= $this->roundPrecision;
        /** @phpstan-var 1|2|3|4 $roundMode */
        $roundMode = $mode ?? $this->roundMode;

        $rounded = \round((float)$value, $precision, $roundMode);

        return \number_format($rounded, $precision, $this->decimalSeparator, $this->thousandsSeparator);
    }

    public function sub(string $minuend, string $subtrahend, ?int $precision = null, ?int $mode = null): string
    {
        return $this->round(\bcsub($minuend, $subtrahend, $this->scale), $precision, $mode);
    }
}
