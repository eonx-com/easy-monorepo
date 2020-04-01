<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Expressions\Interfaces;

/**
 * @deprecated since 2.3.7
 */
interface ExpressionFunctionFactoryInterface
{
    /**
     * @param mixed $expressionFunction
     */
    public function create($expressionFunction): ExpressionFunctionInterface;
}
