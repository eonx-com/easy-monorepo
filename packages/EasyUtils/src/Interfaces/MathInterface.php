<?php

declare(strict_types=1);

namespace EonX\EasyUtils\Interfaces;

interface MathInterface
{
    public const ROUND_DECIMAL_SEPARATOR = '.';

    public const ROUND_MODE = \PHP_ROUND_HALF_EVEN;

    public const ROUND_PRECISION = 0;

    public const ROUND_THOUSANDS_SEPARATOR = '';

    public const SCALE = 99;

    /**
     * @param int|null $mode [optional] <p>
     *                       One of PHP_ROUND_HALF_UP,
     *                       PHP_ROUND_HALF_DOWN,
     *                       PHP_ROUND_HALF_EVEN, or
     *                       PHP_ROUND_HALF_ODD.
     *                       </p>
     */
    public function abs(string $value, ?int $precision = null, ?int $mode = null): string;

    /**
     * @param int|null $mode [optional] <p>
     *                       One of PHP_ROUND_HALF_UP,
     *                       PHP_ROUND_HALF_DOWN,
     *                       PHP_ROUND_HALF_EVEN, or
     *                       PHP_ROUND_HALF_ODD.
     *                       </p>
     */
    public function add(string $augend, string $addend, ?int $precision = null, ?int $mode = null): string;

    public function comp(string $leftOperand, string $rightOperand): int;

    public function compareThat(string $leftOperand): MathComparisonInterface;

    /**
     * @param int|null $mode [optional] <p>
     *                       One of PHP_ROUND_HALF_UP,
     *                       PHP_ROUND_HALF_DOWN,
     *                       PHP_ROUND_HALF_EVEN, or
     *                       PHP_ROUND_HALF_ODD.
     *                       </p>
     */
    public function divide(string $dividend, string $divisor, ?int $precision = null, ?int $mode = null): string;

    /**
     * @param int|null $mode [optional] <p>
     *                       One of PHP_ROUND_HALF_UP,
     *                       PHP_ROUND_HALF_DOWN,
     *                       PHP_ROUND_HALF_EVEN, or
     *                       PHP_ROUND_HALF_ODD.
     *                       </p>
     */
    public function multiply(
        string $multiplicand,
        string $multiplier,
        ?int $precision = null,
        ?int $mode = null,
    ): string;

    /**
     * @param int|null $mode [optional] <p>
     *                       One of PHP_ROUND_HALF_UP,
     *                       PHP_ROUND_HALF_DOWN,
     *                       PHP_ROUND_HALF_EVEN, or
     *                       PHP_ROUND_HALF_ODD.
     *                       </p>
     */
    public function round(string $value, ?int $precision = null, ?int $mode = null): string;

    /**
     * @param int|null $mode [optional] <p>
     *                       One of PHP_ROUND_HALF_UP,
     *                       PHP_ROUND_HALF_DOWN,
     *                       PHP_ROUND_HALF_EVEN, or
     *                       PHP_ROUND_HALF_ODD.
     *                       </p>
     */
    public function sub(string $minuend, string $subtrahend, ?int $precision = null, ?int $mode = null): string;
}
