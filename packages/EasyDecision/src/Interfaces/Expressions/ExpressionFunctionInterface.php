<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyDecision\Interfaces\Expressions;

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
