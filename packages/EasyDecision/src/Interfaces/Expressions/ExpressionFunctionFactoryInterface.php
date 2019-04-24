<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyDecision\Interfaces\Expressions;

interface ExpressionFunctionFactoryInterface
{
    /**
     * Create expression function for given value.
     *
     * @param mixed $expressionFunction
     *
     * @return \LoyaltyCorp\EasyDecision\Interfaces\Expressions\ExpressionFunctionInterface
     *
     * @throws \LoyaltyCorp\EasyDecision\Exceptions\InvalidArgumentException
     */
    public function create($expressionFunction): ExpressionFunctionInterface;
}

\class_alias(
    ExpressionFunctionFactoryInterface::class,
    'StepTheFkUp\EasyDecision\Interfaces\Expressions\ExpressionFunctionFactoryInterface',
    false
);
