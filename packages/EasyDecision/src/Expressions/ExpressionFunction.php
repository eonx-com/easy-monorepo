<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyDecision\Expressions;

use StepTheFkUp\EasyDecision\Interfaces\Expressions\ExpressionFunctionInterface;

final class ExpressionFunction implements ExpressionFunctionInterface
{
    /**
     * @var callable
     */
    private $evaluator;

    /**
     * @var string
     */
    private $name;

    /**
     * ExpressionFunction constructor.
     *
     * @param string $name
     * @param callable $evaluator
     */
    public function __construct(string $name, callable $evaluator)
    {
        $this->name = $name;
        $this->evaluator = $evaluator;
    }

    /**
     * Get callable to evaluate function.
     *
     * @return callable
     */
    public function getEvaluator(): callable
    {
        return $this->evaluator;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}

\class_alias(
    ExpressionFunction::class,
    'LoyaltyCorp\EasyDecision\Expressions\ExpressionFunction',
    false
);
