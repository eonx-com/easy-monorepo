<?php
declare(strict_types=1);

namespace EonX\EasyDecision\ExpressionFunction;

use Closure;

final class ExpressionFunction implements ExpressionFunctionInterface
{
    private readonly Closure $evaluator;

    public function __construct(
        private readonly string $name,
        callable $evaluator,
        private readonly ?string $description = null,
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
