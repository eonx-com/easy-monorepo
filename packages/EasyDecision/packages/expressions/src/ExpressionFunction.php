<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Expressions;

use Closure;
use EonX\EasyDecision\Expressions\Interfaces\ExpressionFunctionInterface;

final class ExpressionFunction implements ExpressionFunctionInterface
{
    private Closure $evaluator;

    public function __construct(
        private string $name,
        callable $evaluator,
        private ?string $description = null,
    ) {
        $this->evaluator = $evaluator(...);
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getEvaluator(): callable
    {
        return $this->evaluator;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
