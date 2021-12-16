<?php

declare(strict_types=1);

namespace EonX\EasyUtils\Interfaces;

interface MathInterface
{
    /**
     * @var string
     */
    public const ROUND_DECIMAL_SEPARATOR = '.';

    /**
     * @var int
     */
    public const ROUND_MODE = \PHP_ROUND_HALF_EVEN;

    /**
     * @var int
     */
    public const ROUND_PRECISION = 0;

    /**
     * @var string
     */
    public const ROUND_THOUSANDS_SEPARATOR = '';

    /**
     * @var int
     */
    public const SCALE = 99;

    public function abs(string $value, ?int $precision = null, ?int $mode = null): string;

    public function add(string $augend, string $addend, ?int $precision = null, ?int $mode = null): string;

    public function comp(string $leftOperand, string $rightOperand): int;

    public function compareThat(string $leftOperand): self;

    public function divide(string $dividend, string $divisor, ?int $precision = null, ?int $mode = null): string;

    public function equalTo(string $rightOperand): bool;

    public function greaterOrEqualTo(string $rightOperand): bool;

    public function greaterThan(string $rightOperand): bool;

    public function lessOrEqualTo(string $rightOperand): bool;

    public function lessThan(string $rightOperand): bool;

    public function multiply(
        string $multiplicand,
        string $multiplier,
        ?int $precision = null,
        ?int $mode = null
    ): string;

    public function round(string $value, ?int $precision = null, ?int $mode = null): string;

    public function sub(string $minuend, string $subtrahend, ?int $precision = null, ?int $mode = null): string;
}
