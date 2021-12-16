<?php

declare(strict_types=1);

namespace EonX\EasyUtils\Interfaces;

interface MathComparisonInterface
{
    public function equalTo(string $rightOperand): bool;

    public function greaterOrEqualTo(string $rightOperand): bool;

    public function greaterThan(string $rightOperand): bool;

    public function lessOrEqualTo(string $rightOperand): bool;

    public function lessThan(string $rightOperand): bool;

    public function setLeftOperand(string $leftOperand): self;
}
