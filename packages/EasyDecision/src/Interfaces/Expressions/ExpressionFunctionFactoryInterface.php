<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyDecision\Interfaces\Expressions;

interface ExpressionFunctionFactoryInterface
{
    /**
     * Create expression function for given value.
     *
     * @param mixed $expressionFunction
     *
     * @return \StepTheFkUp\EasyDecision\Interfaces\Expressions\ExpressionFunctionInterface
     *
     * @throws \StepTheFkUp\EasyDecision\Exceptions\InvalidArgumentException
     */
    public function create($expressionFunction): ExpressionFunctionInterface;
}
