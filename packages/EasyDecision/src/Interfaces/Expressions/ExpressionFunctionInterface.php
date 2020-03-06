<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Interfaces\Expressions;

interface ExpressionFunctionInterface
{
    public function getDescription(): ?string;

    public function getEvaluator(): callable;

    public function getName(): string;
}
