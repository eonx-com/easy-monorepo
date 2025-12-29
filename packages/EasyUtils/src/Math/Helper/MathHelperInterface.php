<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Math\Helper;

interface MathHelperInterface
{
    /**
     * @param numeric-string $value
     * @param int|null $mode [optional] <p>
     * One of PHP_ROUND_HALF_UP,
     * PHP_ROUND_HALF_DOWN,
     * PHP_ROUND_HALF_EVEN, or
     * PHP_ROUND_HALF_ODD.
     * </p>
     */
    public function abs(string $value, ?int $precision = null, ?int $mode = null): string;

    /**
     * @param numeric-string $augend
     * @param numeric-string $addend
     * @param int|null $mode [optional] <p>
     * One of PHP_ROUND_HALF_UP,
     * PHP_ROUND_HALF_DOWN,
     * PHP_ROUND_HALF_EVEN, or
     * PHP_ROUND_HALF_ODD.
     * </p>
     */
    public function add(string $augend, string $addend, ?int $precision = null, ?int $mode = null): string;

    /**
     * @param numeric-string $leftOperand
     * @param numeric-string $rightOperand
     */
    public function comp(string $leftOperand, string $rightOperand): int;

    /**
     * @param numeric-string $leftOperand
     */
    public function compareThat(string $leftOperand): MathComparisonHelperInterface;

    /**
     * @param numeric-string $dividend
     * @param numeric-string $divisor
     * @param int|null $mode [optional] <p>
     * One of PHP_ROUND_HALF_UP,
     * PHP_ROUND_HALF_DOWN,
     * PHP_ROUND_HALF_EVEN, or
     * PHP_ROUND_HALF_ODD.
     * </p>
     */
    public function divide(string $dividend, string $divisor, ?int $precision = null, ?int $mode = null): string;

    /**
     * @param numeric-string $multiplicand
     * @param numeric-string $multiplier
     * @param int|null $mode [optional] <p>
     * One of PHP_ROUND_HALF_UP,
     * PHP_ROUND_HALF_DOWN,
     * PHP_ROUND_HALF_EVEN, or
     * PHP_ROUND_HALF_ODD.
     * </p>
     */
    public function multiply(
        string $multiplicand,
        string $multiplier,
        ?int $precision = null,
        ?int $mode = null,
    ): string;

    /**
     * @param int|null $mode [optional] <p>
     * One of PHP_ROUND_HALF_UP,
     * PHP_ROUND_HALF_DOWN,
     * PHP_ROUND_HALF_EVEN, or
     * PHP_ROUND_HALF_ODD.
     * </p>
     */
    public function round(string $value, ?int $precision = null, ?int $mode = null): string;

    /**
     * @param numeric-string $minuend
     * @param numeric-string $subtrahend
     * @param int|null $mode [optional] <p>
     * One of PHP_ROUND_HALF_UP,
     * PHP_ROUND_HALF_DOWN,
     * PHP_ROUND_HALF_EVEN, or
     * PHP_ROUND_HALF_ODD.
     * </p>
     */
    public function sub(string $minuend, string $subtrahend, ?int $precision = null, ?int $mode = null): string;
}
