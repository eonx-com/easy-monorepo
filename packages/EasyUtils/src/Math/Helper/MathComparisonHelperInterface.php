<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Math\Helper;

interface MathComparisonHelperInterface
{
    /**
     * @param numeric-string $rightOperand
     */
    public function equalTo(string $rightOperand): bool;

    /**
     * @param numeric-string $rightOperand
     */
    public function greaterOrEqualTo(string $rightOperand): bool;

    /**
     * @param numeric-string $rightOperand
     */
    public function greaterThan(string $rightOperand): bool;

    /**
     * @param numeric-string $rightOperand
     */
    public function lessOrEqualTo(string $rightOperand): bool;

    /**
     * @param numeric-string $rightOperand
     */
    public function lessThan(string $rightOperand): bool;
}
