<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Interfaces\Expressions;

interface ExpressionFunctionFactoryInterface
{
    /**
     * @param mixed $expressionFunction
     */
    public function create($expressionFunction): ExpressionFunctionInterface;
}
