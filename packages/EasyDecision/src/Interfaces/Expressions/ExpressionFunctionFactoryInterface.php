<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Interfaces\Expressions;

interface ExpressionFunctionFactoryInterface
{
    /**
     * Create expression function for given value.
     *
     * @param mixed $expressionFunction
     *
     * @return \EonX\EasyDecision\Interfaces\Expressions\ExpressionFunctionInterface
     *
     * @throws \EonX\EasyDecision\Exceptions\InvalidArgumentException
     */
    public function create($expressionFunction): ExpressionFunctionInterface;
}


