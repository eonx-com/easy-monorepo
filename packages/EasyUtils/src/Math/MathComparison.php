<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Math;

use EonX\EasyUtils\Interfaces\MathComparisonInterface;

final readonly class MathComparison implements MathComparisonInterface
{
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

    private function comp(string $leftOperand, string $rightOperand): int
    {
        return \bccomp($leftOperand, $rightOperand, $this->scale);
    }
}
