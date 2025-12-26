<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Math\Helper;

final readonly class MathComparisonHelper implements MathComparisonHelperInterface
{
    /**
     * @param numeric-string $leftOperand
     */
    public function __construct(
        private string $leftOperand,
        private int $scale,
    ) {
    }

    public function equalTo(string $rightOperand): bool
    {
        return $this->comp($this->leftOperand, $rightOperand) === 0;
    }

    public function greaterOrEqualTo(string $rightOperand): bool
    {
        return $this->comp($this->leftOperand, $rightOperand) !== -1;
    }

    public function greaterThan(string $rightOperand): bool
    {
        return $this->comp($this->leftOperand, $rightOperand) === 1;
    }

    public function lessOrEqualTo(string $rightOperand): bool
    {
        return $this->comp($this->leftOperand, $rightOperand) !== 1;
    }

    public function lessThan(string $rightOperand): bool
    {
        return $this->comp($this->leftOperand, $rightOperand) === -1;
    }

    /**
     * @param numeric-string $leftOperand
     * @param numeric-string $rightOperand
     */
    private function comp(string $leftOperand, string $rightOperand): int
    {
        return \bccomp($leftOperand, $rightOperand, $this->scale);
    }
}
