<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyDecision\Interfaces\Expressions;

interface ExpressionFunctionInterface
{
    /**
     * Get callable to evaluate function.
     *
     * @return callable
     */
    public function getEvaluator(): callable;

    /**
     * Get name.
     *
     * @return string
     */
    public function getName(): string;
}

\class_alias(
    ExpressionFunctionInterface::class,
    'StepTheFkUp\EasyDecision\Interfaces\Expressions\ExpressionFunctionInterface',
    false
);
