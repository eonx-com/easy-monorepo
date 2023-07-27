<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Expressions\Interfaces;

interface ExpressionFunctionInterface
{
    public function getDescription(): ?string;

    public function getEvaluator(): callable;

    public function getName(): string;
}
