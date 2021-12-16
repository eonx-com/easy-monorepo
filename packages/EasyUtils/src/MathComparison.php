<?php

declare(strict_types=1);

namespace EonX\EasyUtils;

use EonX\EasyUtils\Interfaces\MathComparisonInterface;

final class MathComparison implements MathComparisonInterface
{
    /**
     * @var string
     */
    private $leftOperand;

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

    public function setLeftOperand(string $leftOperand): MathComparisonInterface
    {
        $this->leftOperand = $leftOperand;

        return $this;
    }

    private function comp(string $leftOperand, string $rightOperand): int
    {
        return \bccomp($leftOperand, $rightOperand, $this->scale);
    }
}
