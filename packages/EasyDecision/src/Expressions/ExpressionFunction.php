<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Expressions;

use EonX\EasyDecision\Interfaces\Expressions\ExpressionFunctionInterface;

final class ExpressionFunction implements ExpressionFunctionInterface
{
    /**
     * @var null|string
     */
    private $description;

    /**
     * @var callable
     */
    private $evaluator;

    /**
     * @var string
     */
    private $name;

    public function __construct(string $name, callable $evaluator, ?string $description = null)
    {
        $this->name = $name;
        $this->evaluator = $evaluator;
        $this->description = $description;
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
