<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyDecision\Interfaces\Expressions;

interface ExpressionFunctionProviderInterface
{
    /**
     * Get list of functions.
     *
     * @return \StepTheFkUp\EasyDecision\Interfaces\Expressions\ExpressionFunctionInterface[]
     */
    public function getFunctions(): array;
}
